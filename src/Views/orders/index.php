<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Management | Astral Cloud</title>
    <link rel="stylesheet" href="/css/base.css">
    <style>
        body { background:#0a0a0a; color:#e0e0e0; font-family:'Segoe UI',system-ui,sans-serif; min-height:100vh; }
        .orders-wrap { max-width:1200px; margin:0 auto; padding:32px 24px 64px; }
        .orders-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:32px; }
        .orders-header h1 { font-size:28px; font-weight:800; color:#fff; margin:0; }
        .orders-header h1 span { color:#38bdf8; }
        .back-link { padding:10px 20px; border-radius:12px; border:1px solid rgba(255,255,255,0.12); color:#b8b8b8; text-decoration:none; font-size:14px; transition:all 0.2s; }
        .back-link:hover { border-color:#38bdf8; color:#38bdf8; }
        .alert-box { padding:16px 20px; border-radius:16px; margin-bottom:24px; font-size:14px; display:flex; align-items:center; gap:10px; }
        .alert-success { background:#002a16; color:#b4ffd8; border:1px solid rgba(255,255,255,0.08); }
        .alert-error { background:#2a0000; color:#ffb4b4; border:1px solid rgba(255,255,255,0.08); }
        .order-card { padding:24px; border-radius:24px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); display:flex; flex-direction:column; }
        .order-card h3 { font-size:18px; font-weight:700; color:#38bdf8; margin:0 0 4px; }
        .order-meta { display:flex; justify-content:space-between; align-items:center; padding-bottom:16px; border-bottom:1px solid rgba(255,255,255,0.06); margin-bottom:16px; flex-wrap:wrap; gap:8px; }
        .order-date { color:#6b7280; font-size:13px; }
        .status-badge { padding:4px 14px; border-radius:999px; font-size:12px; font-weight:800; }
        .status-pending { background:rgba(251,191,36,0.15); color:#fbbf24; }
        .status-confirmed { background:rgba(56,189,248,0.15); color:#38bdf8; }
        .status-provisioning { background:rgba(56,189,248,0.15); color:#38bdf8; }
        .status-active,.status-success { background:rgba(74,222,128,0.15); color:#4ade80; }
        .status-cancelled { background:rgba(239,68,68,0.15); color:#ef4444; }
        .product-name { font-size:16px; font-weight:600; color:#fff; }
        .product-spec { color:#6b7280; font-size:13px; margin:2px 0; }
        .product-price { color:#38bdf8; font-weight:700; font-size:15px; margin-top:8px; }
        .service-box { margin-top:16px; padding:16px; border-radius:16px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.06); }
        .service-row { display:flex; justify-content:space-between; align-items:center; padding:4px 0; font-size:13px; flex-wrap:wrap; gap:4px; }
        .service-label { color:#6b7280; }
        .service-value { color:#b8b8b8; font-weight:600; font-family:'Courier New',monospace; }
        .pwd-wrap { display:flex; align-items:center; gap:6px; }
        .pwd-wrap input { background:transparent; border:none; color:#fbbf24; font-family:'Courier New',monospace; font-size:13px; padding:4px 0; width:auto; }
        .pwd-wrap input:focus { outline:none; }
        .pwd-toggle { background:none; border:1px solid rgba(255,255,255,0.12); color:#6b7280; padding:4px 10px; border-radius:6px; cursor:pointer; font-size:12px; }
        .pwd-toggle:hover { border-color:#38bdf8; color:#38bdf8; }
        .service-expiry { text-align:right; color:#6b7280; font-size:12px; margin-top:8px; }
        .order-actions { display:flex; gap:8px; margin-top:auto; padding-top:16px; }
        .action-btn { padding:10px 16px; border-radius:12px; font-size:13px; font-weight:600; text-decoration:none; cursor:pointer; border:none; transition:all 0.2s; flex:1; text-align:center; }
        .action-btn:hover { transform:translateY(-1px); }
        .action-btn.primary { background:#38bdf8; color:#000; }
        .action-btn.primary:hover { background:#7dd3fc; }
        .action-btn.secondary { border:1px solid rgba(255,255,255,0.14); background:transparent; color:#b8b8b8; }
        .action-btn.secondary:hover { border-color:#38bdf8; color:#38bdf8; }
        .action-btn.danger { border:1px solid rgba(239,68,68,0.3); background:transparent; color:#ef4444; }
        .action-btn.danger:hover { background:rgba(239,68,68,0.1); }
        .action-btn:disabled { opacity:0.4; cursor:not-allowed; transform:none; }
        .grid-3 { display:grid; grid-template-columns:repeat(auto-fill,minmax(360px,1fr)); gap:20px; }
        .empty-state { text-align:center; padding:80px 40px; border-radius:24px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); }
        .empty-state h2 { color:#6b7280; font-size:22px; }
        .empty-state p { color:#6b7280; margin:8px 0 20px; }
        @media(max-width:600px){ .grid-3 { grid-template-columns:1fr; } .orders-wrap { padding:20px 16px; } .order-actions { flex-direction:column; } }
    </style>
</head>
<body>
    <div class="orders-wrap">
        <div class="orders-header">
            <h1>Your <span>Services</span></h1>
            <a href="/" class="back-link">← Back to Home</a>
        </div>

        <?php
        $msg = $_GET['msg'] ?? '';
        $err = $_GET['err'] ?? '';
        $msgMap = [
            'cancelled'    => ['success', '✓ Order cancelled successfully! Voucher code (if any) has been refunded.'],
            'stopped'      => ['success', '✓ VPS stopped successfully.'],
            'started'      => ['success', '✓ VPS is booting — IP and console will be available shortly.'],
            'restarted'    => ['success', '✓ VPS is restarting. Console will reconnect shortly.'],
            'rebuilding'   => ['success', '✓ VPS is rebuilding from base image. This may take a few minutes.'],
        ];
        $displayMsg = '';
        $msgType    = '';
        if ($msg && isset($msgMap[$msg])) {
            [$msgType, $displayMsg] = $msgMap[$msg];
        } elseif ($msg) {
            $msgType = 'error';
            $displayMsg = '✕ ' . htmlspecialchars($msg);
        } elseif ($err === 'cannot_cancel') {
            $msgType = 'error';
            $displayMsg = '✕ This order cannot be canceled.';
        }
        ?>
        <?php if ($displayMsg): ?>
            <div class="alert-box <?= $msgType === 'success' ? 'alert-success' : 'alert-error' ?>"><?= $displayMsg ?></div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div style="font-size:56px;margin-bottom:16px;">📦</div>
                <h2>You have no active services yet</h2>
                <p>Choose a VPS plan to get started.</p>
                <a href="/" class="action-btn primary" style="display:inline-block;padding:12px 32px;">Create a VPS now</a>
            </div>
        <?php else: ?>
            <div class="grid-3">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-meta">
                            <div>
                                <h3>Order #<?= htmlspecialchars($order['order_id']) ?></h3>
                                <div class="order-date"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                            </div>
                            <?php
                                $status = $order['order_status'];
                                $sClass = 'status-pending';
                                if ($status === 'confirmed') $sClass = 'status-confirmed';
                                elseif ($status === 'provisioning') $sClass = 'status-provisioning';
                                elseif ($status === 'active' || $status === 'success') $sClass = 'status-active';
                                elseif ($status === 'cancelled') $sClass = 'status-cancelled';
                            ?>
                            <span class="status-badge <?= $sClass ?>"><?= strtoupper($status) ?></span>
                        </div>

                        <div>
                            <div class="product-name"><?= htmlspecialchars($order['product_name']) ?></div>
                            <div class="product-spec">CPU: <?= htmlspecialchars($order['product_cpu']) ?></div>
                            <div class="product-spec">RAM: <?= htmlspecialchars($order['product_ram']) ?></div>
                            <div class="product-price"><?= number_format($order['total_price'], 0, ',', '.') ?> VND / period</div>

                            <?php
                            $currentService = null;
                            foreach ($services as $srv) {
                                if ($srv['order_item_id'] == $order['order_item_id']) {
                                    $currentService = $srv;
                                    break;
                                }
                            }
                            ?>

                            <?php if (!$currentService && in_array($status, ['confirmed', 'provisioning', 'active'])): ?>
                                <div class="service-box" style="display:flex;align-items:center;gap:10px;">
                                    <span style="font-size:16px;">🔄</span>
                                    <span style="font-size:13px;color:#6b7280;">Service is being prepared. Refresh shortly.</span>
                                </div>
                            <?php endif; ?>

                            <?php if ($currentService): ?>
                                <div class="service-box">
                                    <?php
                                    $ip = $currentService['ip_address'];
                                    $provStatus = $currentService['provisioning_status'] ?? 'pending';
                                    $isReady = $provStatus === 'ready';
                                    $hasIp = $ip !== '0.0.0.0' && !empty($ip);
                                    ?>
                                    <?php if (!$isReady): ?>
                                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;padding:10px 14px;background:rgba(56,189,248,0.08);border-radius:12px;border:1px solid rgba(56,189,248,0.15);">
                                            <span style="font-size:16px;">⏳</span>
                                            <div>
                                                <div style="font-size:13px;font-weight:700;color:#38bdf8;">Provisioning in progress</div>
                                                <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                                                    Status: <?= match($provStatus) {
                                                        'pending' => 'Queued',
                                                        'creating_vm' => 'Creating VM',
                                                        'booting' => 'Booting OS',
                                                        'waiting_ip' => 'Waiting for network',
                                                        'preparing_console' => 'Setting up console',
                                                        default => ucfirst($provStatus)
                                                    } ?> — This completes automatically. Please refresh in a moment.
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="service-row">
                                        <span class="service-label">IP Address:</span>
                                        <span class="service-value" style="color:<?= $hasIp ? '#4ade80' : '#6b7280' ?>;">
                                            <?= $hasIp ? htmlspecialchars($ip) : 'Assigning...' ?>
                                        </span>
                                    </div>
                                    <div class="service-row">
                                        <span class="service-label">OS:</span>
                                        <span class="service-value"><?= htmlspecialchars($currentService['os']) ?></span>
                                    </div>
                                    <div class="service-row">
                                        <span class="service-label">Root Password:</span>
                                        <div class="pwd-wrap">
                                            <input type="password" value="<?= htmlspecialchars($currentService['root_password']) ?>" readonly id="pwd-<?= $currentService['id'] ?>">
                                            <button class="pwd-toggle" type="button" onclick="const p=document.getElementById('pwd-<?= $currentService['id'] ?>');p.type=p.type==='password'?'text':'password';this.textContent=p.type==='password'?'Show':'Hide';">Show</button>
                                        </div>
                                    </div>
                                    <div class="service-expiry">Expires: <?= date('d/m/Y', strtotime($currentService['expiry_date'])) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="order-actions">
                            <a href="/orders/invoice?id=<?= $order['order_id'] ?>" class="action-btn secondary">Invoice</a>
                            <?php if ($status === 'success' || $status === 'active'): ?>
                                <button class="action-btn primary" onclick="window.location.href='/console?id=<?= $currentService ? (int)$currentService['id'] : 0 ?>'">▶ Console</button>
                            <?php elseif ($status === 'pending'): ?>
                                <form action="/orders/cancel" method="POST" style="flex:1;" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
                                    <button type="submit" class="action-btn danger" style="width:100%;">✕ Cancel</button>
                                </form>
                            <?php else: ?>
                                <button class="action-btn secondary" disabled>Waiting...</button>
                            <?php endif; ?>
                        </div>

                        <?php if ($currentService && ($currentService['status'] === 'running' || $currentService['status'] === 'stopped')): ?>
                            <div class="order-actions" style="margin-top:8px;">
                                <?php if ($currentService['status'] === 'running'): ?>
                                    <form action="/service/stop" method="POST" style="flex:1;" onsubmit="return confirm('Stop this VPS?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="service_id" value="<?= (int)$currentService['id'] ?>">
                                        <button type="submit" class="action-btn danger">⏹ Stop</button>
                                    </form>
                                    <form action="/service/restart" method="POST" style="flex:1;" onsubmit="return confirm('Restart this VPS?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="service_id" value="<?= (int)$currentService['id'] ?>">
                                        <button type="submit" class="action-btn secondary">↻ Restart</button>
                                    </form>
                                <?php else: ?>
                                    <form action="/service/start" method="POST" style="flex:1;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="service_id" value="<?= (int)$currentService['id'] ?>">
                                        <button type="submit" class="action-btn primary">▶ Start</button>
                                    </form>
                                <?php endif; ?>
                                <form action="/service/rebuild" method="POST" style="flex:1;" onsubmit="return confirm('Rebuild will DESTROY this VPS and re-clone from the base image. All data will be lost. Continue?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="service_id" value="<?= (int)$currentService['id'] ?>">
                                    <button type="submit" class="action-btn secondary" style="border-color:rgba(251,191,36,0.3);color:#fbbf24;">⚠ Rebuild</button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php
                        $metrics = null;
                        if ($currentService && $currentService['status'] === 'running') {
                            $metrics = \App\Models\Service::getLatestMetrics($currentService['id']);
                        }
                        ?>
                        <?php if ($metrics): ?>
                            <div class="service-box" style="margin-top:12px;">
                                <div style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Resource Usage</div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                    <div>
                                        <div style="font-size:11px;color:#6b7280;">CPU Load</div>
                                        <div class="service-value" style="font-size:18px;"><?= number_format($metrics['cpu_load'], 2) ?></div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px;color:#6b7280;">RAM</div>
                                        <div class="service-value" style="font-size:18px;"><?= (int)$metrics['ram_used_mb'] ?> / <?= (int)$metrics['ram_total_mb'] ?> MB</div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px;color:#6b7280;">Disk</div>
                                        <div class="service-value" style="font-size:14px;"><?= number_format($metrics['disk_used_gb'], 1) ?> / <?= number_format($metrics['disk_total_gb'], 1) ?> GB</div>
                                    </div>
                                    <div>
                                        <div style="font-size:11px;color:#6b7280;">Updated</div>
                                        <div class="service-value" style="font-size:12px;color:#6b7280;"><?= date('H:i', strtotime($metrics['collected_at'])) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
