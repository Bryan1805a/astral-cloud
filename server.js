const express = require('express');
const { exec } = require('child_process');
const app = express();
const port = 10001;

app.get('/provision', (req, res) => {
    const orderId   = req.query.order_id  || Date.now();
    const itemId    = req.query.item_id   || '0';
    const vmName    = req.query.name      || `VPS_Order_${orderId}_${itemId}`;
    const rootPass  = req.query.password  || 'astral123';

    const vmrun   = '"C:\\Program Files\\VMware\\VMware Workstation\\vmrun.exe"';
    const baseVM  = '"C:\\Users\\Bryan\\Documents\\Virtual Machines\\Ubuntu_Base\\Ubuntu_Base.vmx"';
    const newVMDir = `"C:\\Users\\Bryan\\Documents\\VMs\\${vmName}\\${vmName}.vmx"`;

    console.log(`\n[+] Provisioning VM: ${vmName} (order=${orderId}, item=${itemId})...`);

    const cloneCmd = `${vmrun} clone ${baseVM} ${newVMDir} linked -snapshot=Base_Snapshot -cloneName=${vmName}`;
    const startCmd = `${vmrun} start ${newVMDir} gui`;

    exec(`${cloneCmd} && ${startCmd}`, (error, stdout, stderr) => {
        if (error) {
            console.error(`[-] Error: ${error.message}`);
            return res.status(500).json({ success: false, error: error.message });
        }

        // Optionally set the root password inside the VM via VMware Tools
        // (requires open-vm-tools in the guest)
        // exec(`"${vmrun}" runProgramInGuest "${newVMDir}" -interactive -noWait /usr/bin/passwd root <<< "${rootPass}"`, ...);

        console.log(`[+] ${vmName} started successfully!`);
        res.json({
            success: true,
            message: `VM ${vmName} is booting`,
            hostname: vmName,
            ip: '192.168.x.x (refresh once the guest OS finishes booting)'
        });
    });
});

app.get('/status', (req, res) => {
    const vmName   = req.query.name;
    const newVMDir = `"C:\\Users\\Bryan\\Documents\\VMs\\${vmName}\\${vmName}.vmx"`;
    const vmrun    = '"C:\\Program Files\\VMware\\VMware Workstation\\vmrun.exe"';

    exec(`${vmrun} getGuestIPAddress ${newVMDir} -wait`, (error, stdout) => {
        if (error) {
            return res.json({ success: false, ip: null, message: error.message });
        }
        res.json({ success: true, ip: stdout.trim() });
    });
});

app.listen(port, () => console.log(`VM Bridge API running on port ${port}...`));
