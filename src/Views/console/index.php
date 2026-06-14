<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Console | Astral Cloud</title>
    <link rel="stylesheet" href="/css/base.css">
    <style>
        body { background:#0a0a0a; color:#e0e0e0; font-family:'Courier New',monospace; min-height:100vh; }
        .console-wrap { max-width:700px; margin:0 auto; padding:60px 24px; }
        .console-nav { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:32px; padding:16px 20px; border-radius:16px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); }
        .console-nav h1 { font-size:18px; font-weight:700; color:#38bdf8; margin:0; }
        .console-nav .meta { color:#6b7280; font-size:13px; }
        .console-nav .meta code { color:#fbbf24; background:rgba(0,0,0,0.3); padding:2px 6px; border-radius:4px; }
        .step-list { text-align:left; max-width:480px; margin:0 auto; }
        .step { padding:12px 16px; margin:6px 0; border-radius:12px; display:flex; align-items:center; gap:12px; font-size:15px; }
        .step.done { color:#4ade80; }
        .step.active { color:#38bdf8; background:rgba(56,189,248,0.08); }
        .step.pending { color:#6b7280; }
        .step-icon { font-size:20px; width:28px; text-align:center; }
        .step .badge-progress { padding:3px 10px; border-radius:999px; font-size:11px; font-weight:800; }
        .step.active .badge-progress { background:#38bdf8; color:#000; }
        .step.done .badge-progress { background:#4ade80; color:#000; }
        .cred-box { margin-top:24px; padding:16px 20px; border-radius:16px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.06); text-align:center; font-size:14px; }
        .cred-box code { color:#fbbf24; display:block; margin-top:4px; font-size:16px; }
        .back-btn { padding:10px 20px; border-radius:12px; border:1px solid rgba(255,255,255,0.12); color:#b8b8b8; text-decoration:none; font-size:14px; }
        .back-btn:hover { border-color:#38bdf8; color:#38bdf8; }
        .console-iframe { width:100%; height:calc(100vh - 56px); border:none; }
        .error-box { text-align:center; padding:60px; }
        .error-box p { color:#ef4444; font-size:18px; margin-bottom:16px; }
        .spinner { display:inline-block; width:20px; height:20px; border:2px solid currentColor; border-right-color:transparent; border-radius:50%; animation:spinner .75s linear infinite; vertical-align:middle; }
        @keyframes spinner { to { transform:rotate(360deg); } }
    </style>
</head>
<body>

<?php if ($consoleUrl && $service && $provisioningStatus === 'ready'): ?>
    <nav class="console-nav" style="margin:0;border-radius:0;border:0;border-bottom:1px solid rgba(255,255,255,0.06);">
        <h1>&gt;_ <?= htmlspecialchars($service['hostname']) ?></h1>
        <div class="meta">
            IP: <?= htmlspecialchars($service['ip_address']) ?> |
            root: <code><?= htmlspecialchars($service['root_password']) ?></code>
        </div>
    </nav>
    <iframe class="console-iframe" src="<?= htmlspecialchars($consoleUrl) ?>" title="Web Console"></iframe>

<?php elseif ($service): ?>
    <?php
    $steps = [
        'creating_vm'      => ['Creating VM',       'VM is being cloned from base image'],
        'booting'          => ['Booting VM',         'Virtual machine is starting up'],
        'waiting_ip'       => ['Waiting for IP',     'Obtaining IP address from DHCP'],
        'setting_password' => ['Setting Password',   'Configuring root credentials'],
        'preparing_console'=> ['Preparing Console',  'Starting web terminal service'],
        'ready'            => ['Ready',              'Console is available'],
    ];
    $status = $provisioningStatus ?: 'creating_vm';
    $statusIndex = array_search($status, array_keys($steps));
    if ($statusIndex === false) $statusIndex = 0;
    ?>
    <div class="console-wrap">
        <div style="text-align:center;">
            <div style="font-size:56px;margin-bottom:16px;"><?= $statusIndex < 5 ? '⏳' : '✅' ?></div>
            <h1 style="font-size:24px;font-weight:700;margin-bottom:32px;">Provisioning Your VPS</h1>
            <div class="step-list">
                <?php $i = 0; foreach ($steps as $key => $step): ?>
                    <div class="step <?= $i < $statusIndex ? 'done' : ($i === $statusIndex ? 'active' : 'pending') ?>">
                        <span class="step-icon"><?= $i < $statusIndex ? '✓' : ($i === $statusIndex ? '▶' : '○') ?></span>
                        <span><strong><?= $step[0] ?></strong> — <?= $step[1] ?></span>
                        <?php if ($i === $statusIndex && $i < 5): ?>
                            <span class="badge-progress"><span class="spinner"></span> in progress</span>
                        <?php elseif ($i === $statusIndex && $i === 5): ?>
                            <span class="badge-progress">done</span>
                        <?php endif; ?>
                    </div>
                <?php $i++; endforeach; ?>
            </div>
            <div class="cred-box">
                <strong>root password:</strong>
                <code><?= htmlspecialchars($service['root_password']) ?></code>
            </div>
            <p class="text-muted mt-3">This page auto-refreshes. If provisioning takes too long, check back in a minute.</p>
        </div>
    </div>
    <script>setTimeout(function(){location.reload()},15000);</script>

<?php else: ?>
    <div class="console-wrap">
        <div class="error-box">
            <p>Service not found or access denied.</p>
            <a href="/orders" class="back-btn">← Back to Orders</a>
        </div>
    </div>
<?php endif; ?>
</body>
</html>
