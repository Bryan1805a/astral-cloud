const express = require('express');
const http = require('http');
const { exec, spawn } = require('child_process');
const httpProxy = require('http-proxy');

const app = express();
const port = 10001;

// ── ttyd process store ──────────────────────────────────────────
// serviceId -> { process, port, vmIp, startedAt }
const ttydInstances = new Map();
const TTYD_PORT_START = 10010;

// ── Reverse proxy for ttyd ─────────────────────────────────────
const proxy = httpProxy.createProxyServer({
    ws: true,
    timeout: 60000,
    proxyTimeout: 60000,
});

proxy.on('error', (err, req, res) => {
    if (!res) return;
    if (typeof res.writeHead === 'function') {
        if (!res.headersSent) {
            res.writeHead(502, { 'Content-Type': 'text/plain' });
            res.end('Console proxy error');
        }
    } else {
        res.destroy();
    }
});

function getTtydBinary() {
    if (process.platform === 'win32') {
        const path = require('path');
        const exePath = path.join(__dirname, 'ttyd.exe');
        if (require('fs').existsSync(exePath)) return exePath;
        return 'ttyd.exe';
    }
    return 'ttyd';
}

function safeName(raw) {
    return (raw || '').replace(/[^a-zA-Z0-9._-]/g, '');
}

// VMware paths (customise these for your environment)
const VMRUN  = `"C:\\Program Files\\VMware\\VMware Workstation\\vmrun.exe"`;
const BASE_VM = `"C:\\Users\\Bryan\\Documents\\Virtual Machines\\Ubuntu_Base\\Ubuntu_Base.vmx"`;
const VM_DIR  = `C:\\Users\\Bryan\\Documents\\VMs`;

function vmPaths(vmName) {
    const dir = `${VM_DIR}\\${vmName}`;
    return { dir, vmx: `"${dir}\\${vmName}.vmx"` };
}

// ── VM Provisioning ───────────────────────────────────────────
app.get('/provision', (req, res) => {
    const orderId   = String(req.query.order_id || Date.now()).replace(/[^0-9]/g, '');
    const itemId    = String(req.query.item_id  || '0').replace(/[^0-9]/g, '');
    const vmName    = safeName(req.query.name) || `VPS_Order_${orderId}_${itemId}`;
    const rootPass  = safeName(req.query.password) || 'astral123';

    const { vmx } = vmPaths(vmName);

    console.log(`\n[+] Provisioning VM: ${vmName} (order=${orderId}, item=${itemId})...`);

    // Step 1: linked clone from base VM
    const cloneCmd = `${VMRUN} clone ${BASE_VM} ${vmx} linked -snapshot=Base_Snapshot -cloneName=${vmName}`;

    exec(cloneCmd, (cloneErr, cloneOut, cloneErrOut) => {
        if (cloneErr) {
            const msg = `Clone failed: ${cloneErr.message}`;
            console.error(`[-] ${msg}\n  stderr: ${cloneErrOut}`);
            return res.status(500).json({ success: false, error: msg });
        }

        // Step 2: start VM headless (nogui = no Workstation popup)
        const startCmd = `${VMRUN} start ${vmx} nogui`;

        exec(startCmd, (startErr, startOut, startErrOut) => {
            if (startErr) {
                const msg = `Start failed: ${startErr.message}`;
                console.error(`[-] ${msg}\n  stderr: ${startErrOut}`);
                return res.status(500).json({ success: false, error: msg });
            }

            console.log(`[+] ${vmName} started successfully!`);
            res.json({
                success: true,
                message: `VM ${vmName} is booting`,
                hostname: vmName,
                ip: '192.168.x.x (refresh once the guest OS finishes booting)'
            });
        });
    });
});

app.get('/status', (req, res) => {
    const vmName = safeName(req.query.name);
    if (!vmName) return res.json({ success: false, ip: null, message: 'Missing name' });
    const { vmx } = vmPaths(vmName);

    exec(`${VMRUN} getGuestIPAddress ${vmx} -wait`, (error, stdout, stderr) => {
        if (error) {
            console.error(`[-] getGuestIPAddress error for ${vmName}: ${error.message}`);
            return res.json({ success: false, ip: null, message: error.message });
        }
        const ip = stdout.trim();
        console.log(`[+] ${vmName} IP = ${ip}`);
        res.json({ success: true, ip });
    });
});

// ── ttyd Console Management ───────────────────────────────────

app.get('/ttyd/start', (req, res) => {
    const serviceId = String(req.query.service_id || '').replace(/[^0-9]/g, '');
    const vmIp      = req.query.ip || '';
    const vmName    = safeName(req.query.name) || `VPS_${serviceId}`;

    if (!serviceId || !vmIp) {
        return res.json({ success: false, error: 'Missing service_id or ip' });
    }

    if (ttydInstances.has(serviceId)) {
        const existing = ttydInstances.get(serviceId);
        return res.json({ success: true, port: existing.port });
    }

    let port = TTYD_PORT_START;
    const used = new Set([...ttydInstances.values()].map(i => i.port));
    while (used.has(port)) port++;

    const ttydBin = getTtydBinary();
    const knownHostsFile = process.platform === 'win32' ? 'NUL' : '/dev/null';
    const ttyd = spawn(ttydBin, [
        '-p', port.toString(),
        '-W',
        // omit -P (ping interval); ttyd 1.7 defaults are fine
        '-i', '127.0.0.1',
        'ssh',
        '-o', 'StrictHostKeyChecking=no',
        '-o', `UserKnownHostsFile=${knownHostsFile}`,
        `root@${vmIp}`
    ], {
        // windowsHide (CREATE_NO_WINDOW) crashes ttyd 1.7 on Windows — omit it
        stdio: ['ignore', 'ignore', 'pipe']
    });

    let stderrBuf = '';
    ttyd.stderr.on('data', (chunk) => { stderrBuf += chunk.toString(); });

    ttyd.on('error', (err) => {
        console.error(`[-] ttyd spawn error for service #${serviceId}: ${err.message}`);
        ttydInstances.delete(serviceId);
    });

    ttyd.on('exit', (code) => {
        console.log(`[-] ttyd for service #${serviceId} exited (code ${code})`);
        if (code !== 0) {
            console.error(`  stderr: ${stderrBuf || '(none)'}`);
        }
        ttydInstances.delete(serviceId);
    });

    ttyd.unref();
    ttydInstances.set(serviceId, { process: ttyd, port, vmIp, vmName, startedAt: new Date() });

    console.log(`[+] ttyd started for service #${serviceId} → :${port} → root@${vmIp}`);
    res.json({ success: true, port });
});

app.get('/ttyd/stop', (req, res) => {
    const serviceId = String(req.query.service_id || '').replace(/[^0-9]/g, '');
    if (!serviceId) return res.json({ success: false, error: 'Missing service_id' });

    if (ttydInstances.has(serviceId)) {
        const inst = ttydInstances.get(serviceId);
        try {
            process.kill(-inst.process.pid, 'SIGTERM');
        } catch (_) {
            try { inst.process.kill('SIGTERM'); } catch (_2) {}
        }
        ttydInstances.delete(serviceId);
        console.log(`[-] ttyd for service #${serviceId} stopped`);
    }
    res.json({ success: true });
});

app.get('/ttyd/status', (req, res) => {
    const serviceId = String(req.query.service_id || '').replace(/[^0-9]/g, '');
    if (!serviceId || !ttydInstances.has(serviceId)) {
        return res.json({ success: false, running: false });
    }
    const inst = ttydInstances.get(serviceId);
    const alive = !!inst.process && inst.process.exitCode === null;
    if (!alive) {
        ttydInstances.delete(serviceId);
        return res.json({ success: false, running: false });
    }
    res.json({ success: true, running: true, port: inst.port, ip: inst.vmIp });
});

// ── Console Proxy: /console/:serviceId → ttyd on 127.0.0.1:PORT ──
// app.use mounts on the prefix and strips it from req.url automatically.
// This catches /console/123, /console/123/, /console/123/ws, etc.

app.use('/console/:serviceId', (req, res) => {
    const serviceId = req.params.serviceId.replace(/[^0-9]/g, '');
    const inst = ttydInstances.get(serviceId);
    if (!inst) {
        return res.status(404).send('Console not found or not yet provisioned');
    }
    // req.url is now just the sub-path (e.g. /ws, /, or empty)
    proxy.web(req, res, { target: `http://127.0.0.1:${inst.port}` });
});

// ── Start HTTP server ─────────────────────────────────────────
const server = http.createServer(app);

// Handle WebSocket upgrades for the console proxy
server.on('upgrade', (req, socket, head) => {
    const match = req.url.match(/^\/console\/(\d+)/);
    if (match) {
        const serviceId = match[1];
        const inst = ttydInstances.get(serviceId);
        if (inst) {
            const stripped = req.url.replace(new RegExp(`^/console/${serviceId}`), '');
            req.url = stripped;
            proxy.ws(req, socket, head, { target: `http://127.0.0.1:${inst.port}` });
        } else {
            socket.destroy();
        }
    } else {
        socket.destroy();
    }
});

server.listen(port, () => console.log(`VM Bridge API + ttyd proxy running on port ${port}...`));
