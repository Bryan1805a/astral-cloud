<section class="page-section docs-section" style="padding-top:120px;">
    <div class="docs-container">
        <h1><?= __('docs_heading') ?></h1>
        <p class="docs-subtitle"><?= __('docs_subtitle') ?></p>

        <div class="docs-grid">
            <div class="docs-sidebar">
                <h4>Contents</h4>
                <ul>
                    <li><a href="#connect"><?= __('docs_connect') ?></a></li>
                    <li><a href="#terminal"><?= __('docs_terminal') ?></a></li>
                    <li><a href="#manage"><?= __('docs_manage') ?></a></li>
                    <li><a href="#commands"><?= __('docs_commands') ?></a></li>
                    <li><a href="#webserver"><?= __('docs_webserver') ?></a></li>
                    <li><a href="#firewall"><?= __('docs_firewall') ?></a></li>
                    <li><a href="#faq"><?= __('docs_faq') ?></a></li>
                </ul>
            </div>

            <div class="docs-content">
                <!-- CONNECT -->
                <section id="connect" class="docs-card">
                    <h2><?= __('docs_connect') ?></h2>
                    <p><?= __('docs_connect_p1') ?></p>

                    <h3><?= __('docs_connect_h3a') ?></h3>
                    <p><?= __('docs_connect_p2') ?></p>

                    <h3><?= __('docs_connect_h3b') ?></h3>
                    <p><?= __('docs_connect_p3') ?></p>
                    <div class="code-block">
                        <pre><code>ssh root@YOUR_SERVER_IP</code></pre>
                    </div>
                    <p><?= __('docs_connect_p4') ?></p>
                </section>

                <!-- TERMINAL -->
                <section id="terminal" class="docs-card">
                    <h2><?= __('docs_terminal') ?></h2>
                    <p><?= __('docs_terminal_p1') ?></p>
                    <ul>
                        <li><strong><?= strstr(__('docs_terminal_li1'), ': ', true) ?>:</strong> <?= substr(strstr(__('docs_terminal_li1'), ': '), 2) ?></li>
                        <li><strong><?= strstr(__('docs_terminal_li2'), ': ', true) ?>:</strong> <?= substr(strstr(__('docs_terminal_li2'), ': '), 2) ?></li>
                        <li><strong><?= strstr(__('docs_terminal_li3'), ': ', true) ?>:</strong> <?= substr(strstr(__('docs_terminal_li3'), ': '), 2) ?></li>
                        <li><strong><?= strstr(__('docs_terminal_li4'), ': ', true) ?>:</strong> <?= substr(strstr(__('docs_terminal_li4'), ': '), 2) ?></li>
                    </ul>
                </section>

                <!-- MANAGE -->
                <section id="manage" class="docs-card">
                    <h2><?= __('docs_manage') ?></h2>
                    <p><?= __('docs_manage_p1') ?></p>

                    <div class="docs-table">
                        <div class="docs-table-row">
                            <span class="docs-table-label">Start</span>
                            <span class="docs-table-desc"><?= __('docs_manage_start') ?></span>
                        </div>
                        <div class="docs-table-row">
                            <span class="docs-table-label">Stop</span>
                            <span class="docs-table-desc"><?= __('docs_manage_stop') ?></span>
                        </div>
                        <div class="docs-table-row">
                            <span class="docs-table-label">Restart</span>
                            <span class="docs-table-desc"><?= __('docs_manage_restart') ?></span>
                        </div>
                        <div class="docs-table-row">
                            <span class="docs-table-label">Rebuild</span>
                            <span class="docs-table-desc"><?= __('docs_manage_rebuild') ?></span>
                        </div>
                    </div>
                </section>

                <!-- COMMANDS -->
                <section id="commands" class="docs-card">
                    <h2><?= __('docs_commands') ?></h2>
                    <p><?= __('docs_commands_p1') ?></p>

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
                    <h2><?= __('docs_webserver') ?></h2>
                    <p><?= __('docs_webserver_p1') ?></p>

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
                    <h2><?= __('docs_firewall') ?></h2>
                    <p><?= __('docs_firewall_p1') ?></p>
                    <div class="code-block">
                        <pre><code># Enable UFW firewall
sudo ufw allow 22/tcp      # SSH
sudo ufw allow 80/tcp      # HTTP
sudo ufw allow 443/tcp     # HTTPS
sudo ufw enable

# Check status
sudo ufw status</code></pre>
                    </div>
                    <p><?= __('docs_firewall_p2') ?></p>
                </section>

                <!-- FAQ -->
                <section id="faq" class="docs-card">
                    <h2><?= __('docs_faq_title') ?></h2>

                    <div class="faq-item">
                        <h4><?= __('docs_faq_q1') ?></h4>
                        <p><?= __('docs_faq_a1') ?></p>
                    </div>
                    <div class="faq-item">
                        <h4><?= __('docs_faq_q2') ?></h4>
                        <p><?= __('docs_faq_a2') ?></p>
                    </div>
                    <div class="faq-item">
                        <h4><?= __('docs_faq_q3') ?></h4>
                        <p><?= __('docs_faq_a3') ?></p>
                    </div>
                    <div class="faq-item">
                        <h4><?= __('docs_faq_q4') ?></h4>
                        <p><?= __('docs_faq_a4') ?></p>
                    </div>
                    <div class="faq-item">
                        <h4><?= __('docs_faq_q5') ?></h4>
                        <p><?= __('docs_faq_a5') ?></p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
