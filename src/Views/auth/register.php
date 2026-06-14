<section class="auth-section">
    <div class="auth-card">
        <h1>Start now</h1>
        <p>Create an Astral Cloud account to rent and manage VPS.</p>
        <form action="/register" method="POST">
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
                <label>Full Name</label>
                <input type="text" name="name" placeholder="John Smith" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Phone number</label>
                <input type="text" name="phone" placeholder="+84 123 456 789" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="At least 6 characters" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="auth-btn">Create account</button>
        </form>
        <p class="auth-link">Already have an account? <a href="/login">Login</a></p>
    </div>
</section>
