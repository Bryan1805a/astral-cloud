<?php
$statusColor = $service['status'] === 'running' ? '#4ade80' : ($service['status'] === 'stopped' ? '#fbbf24' : '#6b7280');
$ready = ($service['provisioning_status'] ?? '') === 'ready';
$hasIp = !empty($service['ip_address']) && $service['ip_address'] !== '0.0.0.0';
$tab = $_GET['tab'] ?? 'overview';
$tabs = ['overview', 'networking', 'backup', 'activity', 'recovery', 'cancel'];
if (!in_array($tab, $tabs)) $tab = 'overview';
?>

<section class="page-section" style="padding-top:120px;">
<div class="vps-detail-container">
    <a href="/orders" class="back-link-detail">← Back to Orders</a>

    <div class="vps-top-bar">
        <div>
            <h1><?= htmlspecialchars($service['hostname']) ?> <span style="font-size:14px;font-weight:400;color:<?= $statusColor ?>;">● <?= ucfirst($service['status']) ?></span></h1>
            <p style="color:#6b7280;font-size:14px;"><?= htmlspecialchars($service['product_name']) ?> — <?= htmlspecialchars($service['product_cpu']) ?> / <?= htmlspecialchars($service['product_ram']) ?></p>
        </div>
        <div style="text-align:right;">
            <?php if ($ready): ?>
                <a href="/console?id=<?= (int)$service['id'] ?>" style="display:inline-block;padding:10px 20px;border-radius:12px;background:#38bdf8;color:#000;font-weight:700;text-decoration:none;font-size:14px;">▶ Console</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs -->
    <div class="vps-tabs">
        <a href="/vps?id=<?= $service['id'] ?>&tab=overview"    class="vps-tab <?= $tab === 'overview' ? 'active' : '' ?>">Overview</a>
        <a href="/vps?id=<?= $service['id'] ?>&tab=networking"  class="vps-tab <?= $tab === 'networking' ? 'active' : '' ?>">Networking</a>
        <a href="/vps?id=<?= $service['id'] ?>&tab=backup"      class="vps-tab <?= $tab === 'backup' ? 'active' : '' ?>">Backup</a>
        <a href="/vps?id=<?= $service['id'] ?>&tab=activity"    class="vps-tab <?= $tab === 'activity' ? 'active' : '' ?>">Activity</a>
        <a href="/vps?id=<?= $service['id'] ?>&tab=recovery"    class="vps-tab <?= $tab === 'recovery' ? 'active' : '' ?>">Recovery</a>
        <a href="/vps?id=<?= $service['id'] ?>&tab=cancel"      class="vps-tab <?= $tab === 'cancel' ? 'active' : '' ?>">Cancel</a>
    </div>

    <!-- Tab content -->
    <div class="vps-tab-content">

    <?php if ($tab === 'overview'): ?>
        <div class="vps-section">
            <h2>Server Details</h2>
            <div class="vps-grid-detail">
                <div class="vps-stat">
                    <small>Status</small>
                    <strong style="color:<?= $statusColor ?>;">● <?= ucfirst($service['status']) ?></strong>
                </div>
                <div class="vps-stat">
                    <small>IP Address</small>
                    <strong><?= $hasIp ? htmlspecialchars($service['ip_address']) : 'Assigning...' ?></strong>
                </div>
                <div class="vps-stat">
                    <small>Root Password</small>
                    <strong style="font-family:monospace;"><?= htmlspecialchars($service['root_password']) ?></strong>
                </div>
                <div class="vps-stat">
                    <small>Operating System</small>
                    <strong><?= htmlspecialchars($service['os']) ?></strong>
                </div>
                <div class="vps-stat">
                    <small>Created</small>
                    <strong><?= date('d/m/Y', strtotime($service['created_at'])) ?></strong>
                </div>
                <div class="vps-stat">
                    <small>Expires</small>
                    <strong><?= date('d/m/Y', strtotime($service['expiry_date'])) ?></strong>
                </div>
            </div>
        </div>

        <?php if ($metrics): ?>
        <div class="vps-section">
            <h2>Resource Usage</h2>
            <div class="vps-grid-detail">
                <div class="vps-stat">
                    <small>CPU Load</small>
                    <strong><?= number_format($metrics['cpu_load'], 2) ?></strong>
                </div>
                <div class="vps-stat">
                    <small>RAM</small>
                    <strong><?= (int)$metrics['ram_used_mb'] ?> / <?= (int)$metrics['ram_total_mb'] ?> MB</strong>
                </div>
                <div class="vps-stat">
                    <small>Disk</small>
                    <strong><?= number_format($metrics['disk_used_gb'], 1) ?> / <?= number_format($metrics['disk_total_gb'], 1) ?> GB</strong>
                </div>
                <div class="vps-stat">
                    <small>Last Checked</small>
                    <strong><?= date('H:i d/m/Y', strtotime($metrics['collected_at'])) ?></strong>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="vps-section">
            <h2>Quick Actions</h2>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <?php if ($service['status'] === 'stopped'): ?>
                    <form action="/service/start" method="POST"><input type="hidden" name="_csrf_token" value="<?= generateCsrfToken() ?>"><input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>"><button type="submit" style="padding:10px 20px;border-radius:12px;border:none;background:#4ade80;color:#000;font-weight:700;cursor:pointer;">▶ Start</button></form>
                <?php elseif ($service['status'] === 'running'): ?>
                    <form action="/service/stop" method="POST" onsubmit="return confirm('Stop this VPS?')"><input type="hidden" name="_csrf_token" value="<?= generateCsrfToken() ?>"><input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>"><button type="submit" style="padding:10px 20px;border-radius:12px;border:1px solid rgba(239,68,68,0.3);background:transparent;color:#ef4444;font-weight:700;cursor:pointer;">⏹ Stop</button></form>
                    <form action="/service/restart" method="POST" onsubmit="return confirm('Restart this VPS?')"><input type="hidden" name="_csrf_token" value="<?= generateCsrfToken() ?>"><input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>"><button type="submit" style="padding:10px 20px;border-radius:12px;border:1px solid rgba(255,255,255,0.14);background:transparent;color:#e2e8f0;font-weight:700;cursor:pointer;">↻ Restart</button></form>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($tab === 'networking'): ?>
        <div class="vps-section">
            <h2>Network Configuration</h2>
            <div class="vps-grid-detail">
                <div class="vps-stat">
                    <small>IPv4 Address</small>
                    <strong><?= $hasIp ? htmlspecialchars($service['ip_address']) : 'Not assigned' ?></strong>
                </div>
                <div class="vps-stat">
                    <small>Gateway</small>
                    <strong><?= $hasIp ? preg_replace('/\.\d+$/', '.1', $service['ip_address']) : 'N/A' ?></strong>
                </div>
                <div class="vps-stat">
                    <small>Netmask</small>
                    <strong>255.255.255.0</strong>
                </div>
                <div class="vps-stat">
                    <small>DNS</small>
                    <strong>8.8.8.8</strong>
                </div>
            </div>
        </div>
        <div class="vps-section">
            <h2>Firewall Rules (UFW)</h2>
            <p style="color:#6b7280;font-size:13px;">Connect via Console and run <code style="background:rgba(56,189,248,0.1);color:#38bdf8;padding:1px 6px;border-radius:4px;">sudo ufw status</code> to view active firewall rules.</p>
            <div class="vps-grid-detail" style="margin-top:12px;">
                <div class="vps-stat"><small>Port 22 (SSH)</small><strong style="color:#4ade80;">ALLOW</strong></div>
                <div class="vps-stat"><small>Port 80 (HTTP)</small><strong style="color:#4ade80;">ALLOW</strong></div>
                <div class="vps-stat"><small>Port 443 (HTTPS)</small><strong style="color:#4ade80;">ALLOW</strong></div>
            </div>
        </div>

    <?php elseif ($tab === 'backup'): ?>
        <div class="vps-section">
            <h2>Backups</h2>
            <p style="color:#6b7280;font-size:14px;text-align:center;padding:40px;">📦 Automated backups are not yet configured for this server.<br><span style="font-size:12px;">You can manually back up your data via the Console using tools like <code style="background:rgba(56,189,248,0.1);color:#38bdf8;padding:1px 6px;border-radius:4px;">tar</code> or <code style="background:rgba(56,189,248,0.1);color:#38bdf8;padding:1px 6px;border-radius:4px;">rsync</code>.</span></p>
        </div>

    <?php elseif ($tab === 'activity'): ?>
        <div class="vps-section">
            <h2>Activity Log</h2>
            <?php if (empty($activity)): ?>
                <p style="color:#6b7280;text-align:center;padding:30px;">No recent activity.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <?php foreach ($activity as $a): ?>
                        <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:rgba(255,255,255,0.03);border-radius:12px;border:1px solid rgba(255,255,255,0.06);">
                            <span style="font-size:11px;color:#6b7280;min-width:130px;"><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></span>
                            <span style="font-size:13px;color:#e2e8f0;"><?= htmlspecialchars($a['description']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($tab === 'recovery'): ?>
        <div class="vps-section">
            <h2>Recovery & Rebuild</h2>
            <p style="color:#94a3b8;font-size:14px;line-height:1.6;">Rebuilding your VPS will destroy all data and re-clone from the base Ubuntu image. This is useful if your server has been compromised or corrupted.</p>
            <div style="padding:20px;background:rgba(251,191,36,0.05);border:1px solid rgba(251,191,36,0.15);border-radius:14px;margin-top:16px;">
                <strong style="color:#fbbf24;">⚠ Warning: This action is irreversible.</strong>
                <p style="color:#6b7280;font-size:13px;margin:8px 0 16px;">All data, installed packages, and configurations will be permanently deleted.</p>
                <form action="/service/rebuild" method="POST" onsubmit="return confirm('This will DESTROY all data. Are you absolutely sure?');">
                    <?= csrfField() ?>
                    <input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>">
                    <button type="submit" style="padding:10px 24px;border-radius:12px;border:1px solid rgba(251,191,36,0.3);background:transparent;color:#fbbf24;font-weight:700;cursor:pointer;">⚠ Rebuild VPS</button>
                </form>
            </div>
        </div>

    <?php elseif ($tab === 'cancel'): ?>
        <div class="vps-section">
            <h2>Cancel Service</h2>
            <p style="color:#94a3b8;font-size:14px;line-height:1.6;">Cancelling your service will permanently delete the virtual machine and all associated data. The corresponding order will also be cancelled.</p>
            <div style="padding:20px;background:rgba(239,68,68,0.05);border:1px solid rgba(239,68,68,0.15);border-radius:14px;margin-top:16px;">
                <strong style="color:#ef4444;">🗑 This action cannot be undone.</strong>
                <p style="color:#6b7280;font-size:13px;margin:8px 0 16px;">Your VM, IP address, and all data will be permanently removed. Any remaining subscription time will be forfeited.</p>
                <p style="color:#6b7280;font-size:13px;margin:8px 0 16px;">To proceed, please go to My Orders, find the corresponding order, and cancel it from there. This ensures proper stock and voucher restoration.</p>
                <a href="/orders" style="display:inline-block;padding:10px 24px;border-radius:12px;border:1px solid rgba(239,68,68,0.3);color:#ef4444;text-decoration:none;font-weight:700;">Go to My Orders</a>
            </div>
        </div>
    <?php endif; ?>

    </div>
</div>
</section>

<style>
.vps-detail-container { max-width:900px; margin:0 auto; padding:0 24px 64px; }
.vps-top-bar { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:24px; }
.vps-top-bar h1 { font-size:24px; font-weight:800; margin:0; }
.vps-tabs { display:flex; gap:4px; margin-bottom:24px; border-bottom:1px solid rgba(255,255,255,0.08); padding-bottom:0; overflow-x:auto; }
.vps-tab { padding:10px 18px; border-radius:10px 10px 0 0; font-size:13px; font-weight:600; color:#6b7280; text-decoration:none; transition:all 0.15s; white-space:nowrap; border-bottom:2px solid transparent; margin-bottom:-1px; }
.vps-tab:hover { color:#e2e8f0; }
.vps-tab.active { color:#38bdf8; border-bottom-color:#38bdf8; }
.vps-tab-content { min-height:300px; }
.vps-section { background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:16px; padding:24px; margin-bottom:16px; }
.vps-section h2 { font-size:16px; font-weight:700; margin:0 0 16px; }
.vps-grid-detail { display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:12px; }
.vps-stat { padding:14px; background:rgba(255,255,255,0.03); border-radius:12px; border:1px solid rgba(255,255,255,0.04); }
.vps-stat small { display:block; font-size:11px; color:#6b7280; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.5px; }
.vps-stat strong { display:block; font-size:14px; color:#e2e8f0; }
@media(max-width:600px){ .vps-tabs { gap:0; } .vps-tab { padding:8px 12px; font-size:12px; } }
</style>
