<section class="page-section docs-section" style="padding-top:120px;">
    <div class="docs-container">
        <h1>Documentation</h1>
        <p class="docs-subtitle">Everything you need to get started with your Astral Cloud VPS.</p>

        <div class="docs-grid">
            <div class="docs-sidebar">
                <h4>Contents</h4>
                <ul>
                    <li><a href="#connect">Connecting to Your VPS</a></li>
                    <li><a href="#terminal">Using the Web Terminal</a></li>
                    <li><a href="#manage">Managing Your VPS</a></li>
                    <li><a href="#commands">Essential Linux Commands</a></li>
                    <li><a href="#webserver">Installing a Web Server</a></li>
                    <li><a href="#firewall">Basic Firewall Setup</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>

            <div class="docs-content">
                <!-- CONNECT -->
                <section id="connect" class="docs-card">
                    <h2>Connecting to Your VPS</h2>
                    <p>After purchasing a VPS plan, you can connect to it in two ways:</p>

                    <h3>1. Web Terminal (Browser)</h3>
                    <p>The fastest way — no software needed. Go to <strong>My Orders</strong> and click the <strong>Console</strong> button next to your service. A terminal will open directly in your browser with full SSH access.</p>

                    <h3>2. SSH Client</h3>
                    <p>For advanced users, connect via any SSH client:</p>
                    <div class="code-block">
                        <pre><code>ssh root@YOUR_SERVER_IP</code></pre>
                    </div>
                    <p>You can find your server IP and root password in <strong>My Orders &rarr; your service details</strong>.</p>
                </section>

                <!-- TERMINAL -->
                <section id="terminal" class="docs-card">
                    <h2>Using the Web Terminal</h2>
                    <p>The web terminal is a full-featured SSH console that runs in your browser. Here's what you can do:</p>
                    <ul>
                        <li><strong>Resize:</strong> Drag the terminal window — it auto-adjusts.</li>
                        <li><strong>Copy/Paste:</strong> Right-click or use <code>Ctrl+Shift+C</code> / <code>Ctrl+Shift+V</code>.</li>
                        <li><strong>Reconnect:</strong> If the connection drops, it reconnects automatically.</li>
                        <li><strong>Multiple sessions:</strong> Open multiple browser tabs for multiple VPS instances.</li>
                    </ul>
                </section>

                <!-- MANAGE -->
                <section id="manage" class="docs-card">
                    <h2>Managing Your VPS</h2>
                    <p>From <strong>My Orders</strong>, you can control your VPS with these actions:</p>

                    <div class="docs-table">
                        <div class="docs-table-row">
                            <span class="docs-table-label">Start</span>
                            <span class="docs-table-desc">Boot the VPS if it's stopped.</span>
                        </div>
                        <div class="docs-table-row">
                            <span class="docs-table-label">Stop</span>
                            <span class="docs-table-desc">Gracefully shut down. Your data is preserved.</span>
                        </div>
                        <div class="docs-table-row">
                            <span class="docs-table-label">Restart</span>
                            <span class="docs-table-desc">Reset the VPS — useful when applying system updates.</span>
                        </div>
                        <div class="docs-table-row">
                            <span class="docs-table-label">Rebuild</span>
                            <span class="docs-table-desc">Wipe the VPS and reinstall from a fresh Ubuntu image. <strong>All data will be lost.</strong></span>
                        </div>
                    </div>
                </section>

                <!-- COMMANDS -->
                <section id="commands" class="docs-card">
                    <h2>Essential Linux Commands</h2>
                    <p>If you're new to Linux, here are the most useful commands:</p>

                    <div class="code-block">
                        <pre><code># Update your system
sudo apt update && sudo apt upgrade -y

# Check disk space
df -h

# Check memory usage
free -h

# Check CPU load
uptime

# List files in current directory
ls -la

# Navigate directories
cd /var/www

# Create a directory
mkdir my-project

# Edit a file
nano filename.txt

# Check network connectivity
ping google.com</code></pre>
                    </div>
                </section>

                <!-- WEB SERVER -->
                <section id="webserver" class="docs-card">
                    <h2>Installing a Web Server</h2>
                    <p>Your VPS ships with Ubuntu. To host a website, install Apache or Nginx.</p>

                    <h3>Option A: Apache</h3>
                    <div class="code-block">
                        <pre><code>sudo apt update
sudo apt install apache2 -y
sudo systemctl enable apache2
sudo systemctl start apache2</code></pre>
                    </div>
                    <p>Your website files go in: <code>/var/www/html/</code></p>

                    <h3>Option B: Nginx + PHP</h3>
                    <div class="code-block">
                        <pre><code>sudo apt update
sudo apt install nginx php8.4-fpm -y
sudo systemctl enable nginx
sudo systemctl start nginx</code></pre>
                    </div>
                </section>

                <!-- FIREWALL -->
                <section id="firewall" class="docs-card">
                    <h2>Basic Firewall Setup</h2>
                    <p>Secure your VPS by allowing only necessary ports:</p>
                    <div class="code-block">
                        <pre><code># Enable UFW firewall
sudo ufw allow 22/tcp      # SSH
sudo ufw allow 80/tcp      # HTTP
sudo ufw allow 443/tcp     # HTTPS
sudo ufw enable

# Check status
sudo ufw status</code></pre>
                    </div>
                    <p>Always keep port 22 open, or you'll lock yourself out!</p>
                </section>

                <!-- FAQ -->
                <section id="faq" class="docs-card">
                    <h2>FAQ</h2>

                    <div class="faq-item">
                        <h4>Can I change my root password?</h4>
                        <p>Yes. Connect via SSH and run: <code>passwd</code></p>
                    </div>
                    <div class="faq-item">
                        <h4>How long does provisioning take?</h4>
                        <p>Usually 1-3 minutes. The VM is cloned and booted automatically after payment.</p>
                    </div>
                    <div class="faq-item">
                        <h4>Can I install any OS?</h4>
                        <p>Your VPS comes with Ubuntu 22.04 LTS. You can install other distributions manually if needed.</p>
                    </div>
                    <div class="faq-item">
                        <h4>What if I mess up my server?</h4>
                        <p>Use the <strong>Rebuild</strong> button in My Orders. It resets your VPS to a fresh Ubuntu installation.</p>
                    </div>
                    <div class="faq-item">
                        <h4>How do I cancel my service?</h4>
                        <p>Go to My Orders, find your order, and click Cancel. Your VPS will be terminated and data deleted.</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
