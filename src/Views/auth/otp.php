<section class="auth-section">
    <div class="auth-card">
        <h1>Verify Your Account</h1>
        <p>A verification code has been sent to<br><strong style="color:#fff;"><?= htmlspecialchars($masked_email) ?></strong></p>
        <form action="/verify-otp" method="POST">
            <?= csrfField() ?>
            <?php if ($error): ?>
                <div style="margin-bottom:16px;padding:12px 14px;border-radius:16px;background:#2a0000;color:#ffb4b4;border:1px solid rgba(255,255,255,0.12);">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($otp_dev): ?>
                <div style="margin-bottom:16px;padding:12px 14px;border-radius:16px;background:#002a3a;color:#b4e0ff;border:1px solid rgba(255,255,255,0.12);">
                    <strong>Dev mode — OTP:</strong>
                    <span style="font-size:1.5rem;font-weight:bold;letter-spacing:4px;display:block;margin-top:4px;"><?= htmlspecialchars($otp_dev) ?></span>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label>Enter 6-digit verification code</label>
                <input type="text" name="otp" maxlength="6" autocomplete="off" required autofocus
                       style="text-align:center;font-size:32px;letter-spacing:8px;font-weight:700;"
                       placeholder="000000">
            </div>
            <button type="submit" class="auth-btn">Verify Account</button>
        </form>
    </div>
</section>
