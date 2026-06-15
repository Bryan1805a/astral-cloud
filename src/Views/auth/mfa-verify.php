<section class="page-section" style="padding-top:140px;display:flex;align-items:flex-start;justify-content:center;">
<div style="width:100%;max-width:420px;">

    <h1 style="font-size:clamp(36px,5vw,52px);font-weight:300;margin-bottom:8px;">Verification</h1>
    <p style="color:#6b7280;margin-bottom:36px;">Enter the 6-digit code from your authenticator app.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="glass-card" style="text-align:center;">
        <div style="font-size:56px;margin-bottom:24px;">&#128274;</div>
        <form method="POST">
            <?= csrfField() ?>
            <div class="form-group" style="max-width:240px;margin:0 auto;">
                <input type="text" name="code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" required autofocus
                       style="text-align:center;font-size:28px;letter-spacing:8px;font-family:'Courier New',monospace;font-weight:700;">
            </div>
            <button type="submit" class="btn-primary" style="margin-top:24px;border-radius:12px;padding:12px 40px;">Verify</button>
        </form>
    </div>

    <div style="text-align:center;margin-top:16px;">
        <a href="/login" style="color:#6b7280;font-size:14px;">Back to login</a>
    </div>

</div>
</section>
