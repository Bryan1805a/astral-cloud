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
    <div class="console-wrap">
        <div style="text-align:center;" id="provision-box">
            <div style="font-size:56px;margin-bottom:16px;">⏳</div>
            <h1 style="font-size:24px;font-weight:700;margin-bottom:32px;">Provisioning Your VPS</h1>
            <div class="step-list" id="step-list">
                <div class="step active">
                    <span class="step-icon">▶</span>
                    <span><strong>Creating VM</strong> — cloning from base image</span>
                    <span class="badge-progress"><span class="spinner"></span> in progress</span>
                </div>
                <div class="step pending">
                    <span class="step-icon">○</span>
                    <span><strong>Booting VM</strong> — virtual machine is starting up</span>
                </div>
                <div class="step pending">
                    <span class="step-icon">○</span>
                    <span><strong>Waiting for IP</strong> — obtaining IP from DHCP</span>
                </div>
                <div class="step pending">
                    <span class="step-icon">○</span>
                    <span><strong>Preparing Console</strong> — starting web terminal</span>
                </div>
            </div>
            <div class="cred-box">
                <strong>root password:</strong>
                <code><?= htmlspecialchars($service['root_password']) ?></code>
            </div>
            <p class="text-muted mt-3" style="color:#6b7280;font-size:13px;">Checking status automatically...</p>
        </div>
    </div>
    <script>
    var serviceId = <?= (int)$service['id'] ?>;
    var steps = ['creating_vm','booting','waiting_ip','preparing_console'];
    var labels = [
        ['Creating VM','cloning from base image'],
        ['Booting VM','virtual machine is starting up'],
        ['Waiting for IP','obtaining IP from DHCP'],
        ['Preparing Console','starting web terminal']
    ];

    function updateStatus() {
        fetch('/api/service-status?id=' + serviceId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) return;
                if (data.ready) { window.location.href = '/console?id=' + serviceId; return; }
                var idx = steps.indexOf(data.status);
                if (idx === -1) idx = steps.length - 1;
                var html = '';
                for (var i = 0; i < steps.length; i++) {
                    var cls = i < idx ? 'done' : (i === idx ? 'active' : 'pending');
                    var icon = i < idx ? '\u2713' : (i === idx ? '\u25B6' : '\u25CB');
                    html += '<div class="step ' + cls + '">';
                    html += '<span class="step-icon">' + icon + '</span>';
                    html += '<span><strong>' + labels[i][0] + '</strong> \u2014 ' + labels[i][1] + '</span>';
                    if (i === idx) html += '<span class="badge-progress"><span class="spinner"></span> in progress</span>';
                    html += '</div>';
                }
                document.getElementById('step-list').innerHTML = html;
            });
    }

    setInterval(updateStatus, 5000);
    updateStatus();
    </script>

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
