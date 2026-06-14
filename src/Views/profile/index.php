<section class="page-section" style="padding-top:140px;display:flex;align-items:flex-start;justify-content:center;">
<div style="width:100%;max-width:680px;">

    <h1 style="font-size:clamp(42px,6vw,64px);font-weight:300;margin-bottom:12px;">My Profile</h1>
    <p style="color:#6b7280;margin-bottom:48px;">Manage your account details and password.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="glass-card mb-4">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#38bdf8,#818cf8);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;color:#0b0b0b;">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div>
                <h2 style="font-size:22px;font-weight:700;"><?= htmlspecialchars($user['name']) ?></h2>
                <div class="d-flex gap-2" style="margin-top:4px;">
                    <span class="badge tier-<?= $user['tier'] ?>"><?= strtoupper($user['tier']) ?></span>
                    <?php if ($user['role'] !== 'user'): ?>
                        <span class="badge bg-info" style="color:#000;"><?= strtoupper($user['role']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">Email</small>
                <p class="fw-bold"><?= htmlspecialchars($user['email']) ?></p>
            </div>
            <div class="col-md-6">
                <small class="text-muted">Total Spent</small>
                <p class="fw-bold"><?= number_format($user['total_spent'], 0, ',', '.') ?> VND</p>
            </div>
            <div class="col-md-6">
                <small class="text-muted">Member Since</small>
                <p class="fw-bold"><?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
            </div>
            <div class="col-md-6">
                <small class="text-muted">Phone</small>
                <p class="fw-bold"><?= htmlspecialchars($user['phone'] ?: '—') ?></p>
            </div>
        </div>
    </div>

    <!-- Edit Profile -->
    <div class="glass-card mb-4">
        <h3 style="font-size:18px;font-weight:700;margin-bottom:24px;">Edit Information</h3>
        <form action="/profile/update" method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="profile">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Optional">
            </div>
            <button type="submit" class="btn-primary w-100" style="border-radius:12px;">Save Changes</button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="glass-card">
        <h3 style="font-size:18px;font-weight:700;margin-bottom:24px;">Change Password</h3>
        <form action="/profile/update" method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="password">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" class="btn-primary w-100" style="border-radius:12px;">Update Password</button>
        </form>
    </div>

</div>
</section>
