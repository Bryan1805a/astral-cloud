<?php $showSecret = chunk_split($secret, 4, ' '); ?>

<section class="page-section" style="padding-top:140px;display:flex;align-items:flex-start;justify-content:center;">
<div style="width:100%;max-width:520px;">

    <h1 style="font-size:clamp(36px,5vw,52px);font-weight:300;margin-bottom:8px;">Setup MFA</h1>
    <p style="color:#6b7280;margin-bottom:36px;">Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="glass-card" style="text-align:center;">
        <div style="background:#fff;display:inline-block;padding:12px;border-radius:16px;margin-bottom:20px;">
            <div id="qrcode" style="width:200px;height:200px;"></div>
        </div>

        <p style="color:#6b7280;font-size:13px;margin-bottom:8px;">Or enter this code manually:</p>
        <code style="display:inline-block;padding:12px 24px;background:rgba(0,0,0,0.4);border-radius:10px;color:#fbbf24;font-family:'Courier New',monospace;font-size:18px;letter-spacing:2px;margin-bottom:28px;">
            <?= htmlspecialchars($showSecret) ?>
        </code>

        <form method="POST">
            <?= csrfField() ?>
            <p style="color:#94a3b8;font-size:13px;margin-bottom:12px;">Enter the 6-digit code from your app to verify:</p>
            <div class="form-group" style="max-width:240px;margin:0 auto;">
                <input type="text" name="code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" required
                       style="text-align:center;font-size:28px;letter-spacing:8px;font-family:'Courier New',monospace;font-weight:700;">
            </div>
            <button type="submit" class="btn-primary" style="margin-top:20px;border-radius:12px;padding:12px 40px;">Verify &amp; Enable</button>
        </form>
    </div>

    <div style="text-align:center;margin-top:16px;">
        <a href="/profile" style="color:#6b7280;font-size:14px;">Cancel</a>
    </div>

</div>
</section>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
new QRCode(document.getElementById('qrcode'), {
    text: <?= json_encode($uri) ?>,
    width: 200,
    height: 200,
    colorDark: '#0f172a',
    colorLight: '#ffffff'
});
</script>
