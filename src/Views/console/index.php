<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Console | Astral Cloud</title>
    <style>
        body {
            background-color: #0c0c0c;
            color: #ffffff; /* Terminal standard green */
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            margin: 0;
            padding: 20px;
            height: 100vh;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        #terminal-output {
            white-space: pre-wrap;
            line-height: 1.5;
        }
        .input-line {
            display: flex;
            align-items: center;
            margin-top: 5px;
        }
        .prompt {
            color: #10b981;; /* Cyan for prompt */
            font-weight: bold;
            white-space: nowrap;
        }
        .cmd-input {
            background: transparent;
            border: none;
            color: #ffffff;
            font-family: inherit;
            font-size: inherit;
            outline: none;
            flex-grow: 1;
            margin-left: 8px;
        }
        /* Cursor blinking effect (optional) */
        .cmd-input::placeholder { color: transparent; }
    </style>
</head>
<body>

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

        // Emulated server boot script
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
                
                // The random stream speed of 50ms to 300ms creates a "realistic" feel.
                setTimeout(printBootSequence, Math.random() * 250 + 50);
            } else {
                // After booting, the command line appears.
                interactive.style.display = 'flex';
                cmdInput.focus();
            }
        }

        // Start booting
        setTimeout(printBootSequence, 500);

        // Handling when the user types a command.
        cmdInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                const val = this.value.trim();
                const currentPrompt = "root@" + hostname + ":~# " + val;
                
                // Print the command line that the user just typed.
                const cmdDiv = document.createElement('div');
                cmdDiv.innerHTML = `<span style="color:#38bdf8;font-weight:bold;">root@${hostname}:~#</span> ${val}`;
                output.appendChild(cmdDiv);

                // Handling command responses
                const responseDiv = document.createElement('div');
                if (val === 'help') {
                    responseDiv.innerHTML = "Available commands:<br> - clear : Clear the terminal screen<br> - ping  : Check network connection<br> - exit  : Close connection";
                } else if (val === 'clear') {
                    output.innerHTML = '';
                } else if (val === 'ping') {
                    responseDiv.innerHTML = "PING 8.8.8.8 (8.8.8.8) 56(84) bytes of data.<br>64 bytes from 8.8.8.8: icmp_seq=1 ttl=117 time=14.2 ms<br>64 bytes from 8.8.8.8: icmp_seq=2 ttl=117 time=14.5 ms";
                } else if (val === 'exit') {
                    window.location.href = '/orders'; // Return to the dashboard page
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

        // Keep the focus on the input field when you click outside of it.
        document.addEventListener('click', () => cmdInput.focus());
    </script>
</body>
</html>