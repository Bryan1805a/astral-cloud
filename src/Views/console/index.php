<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Console | Astral Cloud</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #1a1d23; color: #e0e0e0; font-family: 'Courier New', monospace; }
        .progress-container { max-width: 600px; margin: 80px auto; text-align: center; }
        .status-icon { font-size: 48px; margin-bottom: 16px; }
        .status-label { font-size: 18px; font-weight: bold; margin-bottom: 24px; }
        .progress-steps { text-align: left; display: inline-block; }
        .step { padding: 8px 16px; margin: 4px 0; border-radius: 6px; }
        .step.done { color: #4ade80; }
        .step.active { color: #38bdf8; background: rgba(56,189,248,0.1); }
        .step.pending { color: #6b7280; }
        .step .badge { margin-left: 8px; }
        .cred-bar { background: #2d2d2d; padding: 12px 20px; border-radius: 8px; margin-top: 20px; font-size: 14px; }
        .cred-bar code { color: #fbbf24; }
        iframe { width:100%; height:calc(100vh - 56px); border:none; }
    </style>
</head>
<body>

<?php if ($consoleUrl && $service && $provisioningStatus === 'ready'): ?>
    <!-- ttyd iframe — live terminal -->
    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary px-3">
        <span class="navbar-brand text-info">
            &#62;_ <?= htmlspecialchars($service['hostname']) ?>
        </span>
        <div class="text-light small">
            IP: <?= htmlspecialchars($service['ip_address']) ?> |
            root: <code class="bg-dark text-warning p-1"><?= htmlspecialchars($service['root_password']) ?></code>
        </div>
    </nav>
    <iframe src="<?= htmlspecialchars($consoleUrl) ?>" title="Web Console"></iframe>

<?php elseif ($service): ?>
    <!-- Provisioning progress -->
    <?php
    $steps = [
        'creating_vm'      => ['Creating VM',       'VM is being cloned from base image'],
        'booting'          => ['Booting VM',         'Virtual machine is starting up'],
        'waiting_ip'       => ['Waiting for IP',     'Obtaining IP address from DHCP'],
        'preparing_console'=> ['Preparing Console',  'Starting web terminal service'],
        'ready'            => ['Ready',              'Console is available'],
    ];
    $status = $provisioningStatus ?: 'creating_vm';
    $statusIndex = array_search($status, array_keys($steps));
    if ($statusIndex === false) $statusIndex = 0;
    ?>
    <div class="progress-container">
        <div class="status-icon">
            <?php if ($statusIndex < 4): ?>
                <span style="font-size:64px;">⏳</span>
            <?php else: ?>
                <span style="font-size:64px;">✅</span>
            <?php endif; ?>
        </div>
        <div class="status-label">Provisioning Your VPS</div>
        <div class="progress-steps">
            <?php $i = 0; foreach ($steps as $key => $step): ?>
                <div class="step <?= $i < $statusIndex ? 'done' : ($i === $statusIndex ? 'active' : 'pending') ?>">
                    <?= $i < $statusIndex ? '✓' : ($i === $statusIndex ? '▶' : '○') ?>
                    <strong><?= $step[0] ?></strong>
                    <span class="text-muted">— <?= $step[1] ?></span>
                    <?php if ($i === $statusIndex && $i < 4): ?>
                        <span class="badge bg-info">in progress</span>
                    <?php elseif ($i === $statusIndex && $i === 4): ?>
                        <span class="badge bg-success">done</span>
                    <?php endif; ?>
                </div>
            <?php $i++; endforeach; ?>
        </div>
        <div class="cred-bar">
            <strong>root password:</strong>
            <code><?= htmlspecialchars($service['root_password']) ?></code>
        </div>
        <p class="text-muted mt-3 small">
            This page auto-refreshes. If provisioning takes too long,
            check back in a minute.
        </p>
    </div>

    <script>
        // Auto-refresh every 15 seconds while provisioning
        setTimeout(function() { location.reload(); }, 15000);
    </script>

<?php else: ?>
    <div class="progress-container">
        <p class="text-danger">Service not found or access denied.</p>
        <a href="/orders" class="btn btn-outline-light">Back to Orders</a>
    </div>
<?php endif; ?>
</body>
</html>
