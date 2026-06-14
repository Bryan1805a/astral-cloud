<section class="auth-section">
    <div class="auth-card">
        <h1>Login</h1>
        <p>Login to manage your VPS.</p>
        <form action="/login" method="POST">
            <?= csrfField() ?>
            <?php if ($error): ?>
                <div style="margin-bottom:16px;padding:12px 14px;border-radius:16px;background:#2a0000;color:#ffb4b4;border:1px solid rgba(255,255,255,0.12);">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div style="margin-bottom:16px;padding:12px 14px;border-radius:16px;background:#002a16;color:#b4ffd8;border:1px solid rgba(255,255,255,0.12);">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="auth-btn">Login</button>
        </form>
        <p class="auth-link">Don't have an account? <a href="/register">Register</a></p>
    </div>
</section>
