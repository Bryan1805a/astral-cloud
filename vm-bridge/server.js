/**
 * VM Bridge — VMware provisioning + web SSH console
 *
 * REST API (port 10001):
 *   GET /provision?order_id=&item_id=&name=&password=
 *       Clones Ubuntu_Base VM via vmrun, starts it, returns immediately.
 *   GET /status?name=
 *       Polls the guest OS for its IP address via vmrun getGuestIPAddress.
 *   GET /set-root-password?name=&password=&base_password=
 *       Uses vmrun runProgramInGuest to change root password.
 *
 * Console API:
 *   GET /ttyd/start?service_id=&ip=&name=&password=
 *       Registers a service for web terminal access (called by PHP backend).
 *   GET /ttyd/stop?service_id=
 *       Removes console registration.
 *
 * Web Terminal:
 *   GET /console/:serviceId → xterm.js page
 *   WS  /console/:serviceId → SSH session via ssh2 library
 *
 * The PHP app proxies /console/ → here (see proxy-console.conf).
 */

const express = require('express');
const http = require('http');
const { exec } = require('child_process');
const { Client: SSHClient } = require('ssh2');
const { WebSocketServer } = require('ws');

const app = express();
const port = parseInt(process.env.PORT, 10) || 10001;

// ── Console store ─────────────────────────────────────────────
// serviceId -> { ip, hostname, password, startedAt }
const consoleStore = new Map();

function safeName(raw) {
    return (raw || '').replace(/[^a-zA-Z0-9._-]/g, '');
}

// ── VMware paths (override via env vars or edit below) ──────
const VMRUN   = process.env.VMRUN_PATH   || `"C:\\Program Files\\VMware\\VMware Workstation\\vmrun.exe"`;
const BASE_VM = process.env.BASE_VMX     || `"C:\\Users\\Bryan\\Documents\\Virtual Machines\\Ubuntu_Base\\Ubuntu_Base.vmx"`;
const VM_DIR  = process.env.VM_DIR       || `C:\\Users\\Bryan\\Documents\\VMs`;

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

    const cloneCmd = `${VMRUN} clone ${BASE_VM} ${vmx} linked -snapshot=Base_Snapshot -cloneName=${vmName}`;

    exec(cloneCmd, (cloneErr, cloneOut, cloneErrOut) => {
        if (cloneErr) {
            const msg = `Clone failed: ${cloneErr.message}`;
            console.error(`[-] ${msg}\n  stderr: ${cloneErrOut}`);
            return res.status(500).json({ success: false, error: msg });
        }

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

// ── Set root password on the VM (called after getting IP) ─────
app.get('/set-root-password', (req, res) => {
    const vmName = safeName(req.query.name);
    const newPassword = req.query.password || 'astral123';
    const basePassword = req.query.base_password || 'password';

    if (!vmName) return res.json({ success: false, error: 'Missing name' });
    const { vmx } = vmPaths(vmName);

    const cmd = `${VMRUN} -gu root -gp "${basePassword}" runProgramInGuest ${vmx} /bin/sh -c "echo 'root:${newPassword.replace(/'/g, "'\\''")}' | chpasswd"`;

    console.log(`[+] Setting root password for ${vmName}...`);
    exec(cmd, { timeout: 30000 }, (error, stdout, stderr) => {
        if (error) {
            console.error(`[-] Failed to set password for ${vmName}: ${error.message}`);
            console.error(`    stderr: ${stderr || '(none)'}`);
            return res.json({ success: false, error: error.message });
        }
        console.log(`[+] Root password set for ${vmName}`);
        res.json({ success: true });
    });
});

// ── Console management API (called by PHP backend) ────────────
app.get('/ttyd/start', (req, res) => {
    const serviceId = String(req.query.service_id || '').replace(/[^0-9]/g, '');
    const vmIp      = req.query.ip || '';
    const vmName    = safeName(req.query.name) || `VPS_${serviceId}`;
    const password  = req.query.password || 'astral123';

    if (!serviceId || !vmIp) {
        return res.json({ success: false, error: 'Missing service_id or ip' });
    }

    consoleStore.set(serviceId, { ip: vmIp, hostname: vmName, password, startedAt: new Date() });
    console.log(`[+] Console registered for service #${serviceId} → ${vmIp}`);
    res.json({ success: true, port: 0 });
});

app.get('/ttyd/stop', (req, res) => {
    const serviceId = String(req.query.service_id || '').replace(/[^0-9]/g, '');
    if (!serviceId) return res.json({ success: false, error: 'Missing service_id' });

    consoleStore.delete(serviceId);
    console.log(`[-] Console removed for service #${serviceId}`);
    res.json({ success: true });
});

app.get('/ttyd/status', (req, res) => {
    const serviceId = String(req.query.service_id || '').replace(/[^0-9]/g, '');
    if (!serviceId || !consoleStore.has(serviceId)) {
        return res.json({ success: false, running: false });
    }
    const info = consoleStore.get(serviceId);
    res.json({ success: true, running: true, port: 0, ip: info.ip });
});

// ── xterm.js terminal page ────────────────────────────────────
app.get('/console/:serviceId', (req, res) => {
    const serviceId = String(req.params.serviceId || '').replace(/[^0-9]/g, '');
    const info = consoleStore.get(serviceId);

    if (!info) {
        return res.status(404).contentType('text/html').send(`<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Console</title>
<style>body{background:#0c0c0c;color:#ef4444;font-family:monospace;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}</style>
</head><body><div style="text-align:center"><p style="font-size:18px">Console not found</p>
<p style="color:#6b7280">The service may not be provisioned yet.</p></div></body></html>`);
    }

    res.contentType('text/html').send(`<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${info.hostname} | Astral Cloud Console</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:#0c0c0c;}
        #terminal{height:100vh;padding:4px;}
        .xterm .xterm-viewport::-webkit-scrollbar{width:8px;}
        .xterm .xterm-viewport::-webkit-scrollbar-thumb{background:#333;border-radius:4px;}
        .xterm .xterm-viewport::-webkit-scrollbar-track{background:#0c0c0c;}
    </style>
</head>
<body>
    <div id="terminal"></div>
    <script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-web-links@0.9.0/lib/xterm-addon-web-links.js"></script>
    <script>
(function(){
    var term = new Terminal({
        cursorBlink: true,
        fontSize: 14,
        fontFamily: "'Cascadia Code', 'Fira Code', 'Courier New', monospace",
        theme: {
            background: '#0c0c0c',
            foreground: '#e0e0e0',
            cursor: '#ffffff',
            selectionBackground: '#38bdf8',
            selectionForeground: '#000000',
            black: '#1a1a2e',
            red: '#ff6b6b',
            green: '#4ade80',
            yellow: '#fbbf24',
            blue: '#60a5fa',
            magenta: '#c084fc',
            cyan: '#22d3ee',
            white: '#e0e0e0',
            brightBlack: '#374151',
            brightRed: '#fca5a5',
            brightGreen: '#86efac',
            brightYellow: '#fde68a',
            brightBlue: '#93c5fd',
            brightMagenta: '#d8b4fe',
            brightCyan: '#67e8f9',
            brightWhite: '#ffffff'
        },
        allowProposedApi: true,
        allowTransparency: false,
        scrollback: 5000
    });

    var fitAddon = new FitAddon.FitAddon();
    var webLinksAddon = new WebLinksAddon.WebLinksAddon();
    term.loadAddon(fitAddon);
    term.loadAddon(webLinksAddon);
    term.open(document.getElementById('terminal'));
    fitAddon.fit();

    var proto = location.protocol === 'https:' ? 'wss:' : 'ws:';
    var wsUrl = proto + '//' + location.host + '/console/${serviceId}';
    var ws;
    var reconnectTimer;
    var reconnectDelay = 1000;

    function connect() {
        if (ws && (ws.readyState === WebSocket.OPEN || ws.readyState === WebSocket.CONNECTING)) return;

        ws = new WebSocket(wsUrl);

        ws.onopen = function() {
            reconnectDelay = 1000;
            term.writeln('\\r\\n\u001b[1;32mConnected to ${info.hostname} (${info.ip})\u001b[0m');
            ws.send(JSON.stringify({ type: 'resize', cols: term.cols, rows: term.rows }));
        };

        ws.onmessage = function(ev) {
            term.write(ev.data);
        };

        ws.onclose = function() {
            term.writeln('\\r\\n\u001b[1;33mConnection lost. Reconnecting in ' + (reconnectDelay / 1000) + 's...\u001b[0m');
            reconnectTimer = setTimeout(function() {
                reconnectDelay = Math.min(reconnectDelay * 2, 30000);
                connect();
            }, reconnectDelay);
        };

        ws.onerror = function() {
            // onclose fires right after onerror, let onclose handle reconnection
        };
    }

    term.onData(function(data) {
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ type: 'input', data: data }));
        }
    });

    term.onResize(function(size) {
        if (ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ type: 'resize', cols: size.cols, rows: size.rows }));
        }
    });

    window.addEventListener('resize', function() { fitAddon.fit(); });
    connect();
})();
    </script>
</body>
</html>`);
});

// ── HTTP server ────────────────────────────────────────────────
const server = http.createServer(app);

// ── WebSocket terminal handler ─────────────────────────────────
const wss = new WebSocketServer({ noServer: true });

wss.on('connection', function(ws, req) {
    const match = req.url.match(/^\/console\/(\d+)/);
    if (!match) { ws.close(); return; }

    const serviceId = match[1];
    const info = consoleStore.get(serviceId);

    if (!info) {
        ws.send('\r\n\u001b[1;31mService not provisioned yet.\u001b[0m');
        ws.close();
        return;
    }

    console.log(`[+] Terminal: service #${serviceId} → ssh root@${info.ip}`);

    const ssh = new SSHClient();
    let sshStream;
    let connected = false;

    ssh.on('ready', function() {
        connected = true;
        ssh.shell({ term: 'xterm-256color', cols: 80, rows: 24 }, function(err, stream) {
            if (err) {
                ws.send('\r\n\u001b[1;31mShell error: ' + err.message + '\u001b[0m');
                ws.close();
                return;
            }
            sshStream = stream;

            stream.on('data', function(data) {
                if (ws.readyState === 1) ws.send(data.toString('utf-8'));
            });

            stream.stderr.on('data', function(data) {
                if (ws.readyState === 1) ws.send(data.toString('utf-8'));
            });

            stream.on('close', function() {
                console.log(`[-] SSH closed for #${serviceId}`);
                ws.close();
            });
        });
    });

    ssh.on('error', function(err) {
        console.error(`[-] SSH error #${serviceId}: ${err.message}`);
        if (!connected) {
            ws.send('\r\n\u001b[1;31mCannot connect to VM. It may still be booting.\r\n' +
                    'Error: ' + err.message + '\u001b[0m');
        }
        ws.close();
    });

    ssh.on('close', function() {
        console.log(`[-] SSH connection closed for #${serviceId}`);
    });

    ws.on('message', function(msg) {
        try {
            var parsed = JSON.parse(msg);
            if (parsed.type === 'input' && sshStream) {
                sshStream.write(parsed.data);
            } else if (parsed.type === 'resize' && sshStream) {
                sshStream.setWindow(parsed.rows, parsed.cols);
            }
        } catch (_) {}
    });

    ws.on('close', function() {
        if (sshStream) sshStream.end();
        ssh.end();
    });

    ssh.connect({
        host: info.ip,
        port: 22,
        username: 'root',
        password: info.password || 'astral123',
        readyTimeout: 15000,
        keepaliveInterval: 10000,
        algorithms: {
            kex: [
                'ecdh-sha2-nistp256', 'ecdh-sha2-nistp384', 'ecdh-sha2-nistp521',
                'diffie-hellman-group-exchange-sha256', 'diffie-hellman-group14-sha256',
                'diffie-hellman-group14-sha1'
            ],
            cipher: [
                'aes128-ctr', 'aes192-ctr', 'aes256-ctr',
                'aes128-gcm@openssh.com', 'aes256-gcm@openssh.com'
            ],
            serverHostKey: [
                'ssh-rsa', 'ecdsa-sha2-nistp256', 'ssh-ed25519',
                'rsa-sha2-512', 'rsa-sha2-256'
            ]
        }
    });
});

server.on('upgrade', function(req, socket, head) {
    if (req.url.match(/^\/console\/(\d+)/)) {
        wss.handleUpgrade(req, socket, head, function(ws) {
            wss.emit('connection', ws, req);
        });
    } else {
        socket.destroy();
    }
});

server.listen(port, function() {
    console.log('VM Bridge API running on port ' + port + '...');
});
