<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Console | Astral Cloud</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/console.css">
</head>
<body>

<?php if ($consoleUrl && $service): ?>
    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-3">
        <span class="navbar-brand text-info">
            <i class="bi bi-terminal"></i> <?= htmlspecialchars($service['hostname']) ?>
        </span>
        <div class="text-light small">
            IP: <?= htmlspecialchars($service['ip_address']) ?> |
            User: root |
            Pass: <code class="bg-dark text-warning p-1"><?= htmlspecialchars($service['root_password']) ?></code>
        </div>
    </nav>
    <iframe src="<?= htmlspecialchars($consoleUrl) ?>" id="guac-frame"
            style="width:100%;height:calc(100vh - 56px);border:none;"></iframe>
<?php else: ?>
    <div id="terminal-output"></div>

    <div id="interactive" class="input-line" style="display: none;">
        <span class="prompt">root@<?= htmlspecialchars($hostname) ?>:~#</span>
        <input type="text" id="cmd" class="cmd-input" autocomplete="off" autofocus>
    </div>

    <script>
        const output = document.getElementById('terminal-output');
        const interactive = document.getElementById('interactive');
        const cmdInput = document.getElementById('cmd');
        const hostname = "<?= htmlspecialchars($hostname) ?>";

        const bootSequence = [
            "Initiating connection to Astral Cloud Gateway...",
            "Resolving IP address...",
            "Connected to 103.14.xx.xx (Port 22)",
            "Authenticating SSH keys... [OK]",
            "Mounting /dev/sda1 (NVMe SSD)... [OK]",
            "Starting network interfaces... [OK]",
            "Starting OpenSSH daemon... [OK]",
            " ",
            "Welcome to Ubuntu 22.04.3 LTS (GNU/Linux 5.15.0-101-generic x86_64)",
            " * Documentation:  https://help.ubuntu.com",
            " * Management:     https://landscape.canonical.com",
            " * Support:        https://ubuntu.com/advantage",
            " ",
            "System information as of " + new Date().toUTCString(),
            "  System load:  0.01               Processes:             102",
            "  Usage of /:   12.4% of 80.00GB   Users logged in:       1",
            "  Memory usage: 8%                 IPv4 address for eth0: 103.14.88.99",
            " ",
            "Last login: " + new Date().toLocaleString() + " from 113.160.x.x",
            "Type 'help' for a list of available commands."
        ];

        let lineIndex = 0;

        function printBootSequence() {
            if (lineIndex < bootSequence.length) {
                const line = document.createElement('div');
                line.textContent = bootSequence[lineIndex];
                output.appendChild(line);
                window.scrollTo(0, document.body.scrollHeight);
                lineIndex++;
                setTimeout(printBootSequence, Math.random() * 300 + 100);
            } else {
                interactive.style.display = 'flex';
                cmdInput.focus();
            }
        }

        setTimeout(printBootSequence, 500);

        cmdInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const val = this.value.trim();
                const cmdDiv = document.createElement('div');
                cmdDiv.innerHTML = `<span style="color:#38bdf8;font-weight:bold;">root@${hostname}:~#</span> ${val}`;
                output.appendChild(cmdDiv);

                const responseDiv = document.createElement('div');
                if (val === 'help') {
                    responseDiv.innerHTML = "Available commands:<br> - clear : Clear the terminal screen<br> - ping  : Check network connection<br> - exit  : Close connection";
                } else if (val === 'clear') {
                    output.innerHTML = '';
                } else if (val === 'ping') {
                    responseDiv.innerHTML = "PING 8.8.8.8 (8.8.8.8) 56(84) bytes of data.<br>64 bytes from 8.8.8.8: icmp_seq=1 ttl=117 time=14.2 ms<br>64 bytes from 8.8.8.8: icmp_seq=2 ttl=117 time=14.5 ms";
                } else if (val === 'exit') {
                    window.location.href = '/orders';
                    return;
                } else if (val !== '') {
                    responseDiv.textContent = "-bash: " + val + ": command not found";
                }

                if (val !== 'clear' && val !== 'exit') {
                    output.appendChild(responseDiv);
                }

                this.value = '';
                window.scrollTo(0, document.body.scrollHeight);
            }
        });

        document.addEventListener('click', () => cmdInput.focus());
    </script>
<?php endif; ?>
</body>
</html>
