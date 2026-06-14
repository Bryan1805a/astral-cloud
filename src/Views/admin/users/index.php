<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management | Astral Cloud Admin</title>
    <link rel="stylesheet" href="/css/base.css">
    <style>
        .tier-badge { padding:4px 14px; border-radius:999px; font-size:11px; font-weight:800; display:inline-block; }
        .tier-badge.silver { background:#c0c0c0; color:#000; }
        .tier-badge.gold { background:#ffd700; color:#000; }
        .tier-badge.diamond { background:#00d4ff; color:#000; }
        .user-status { padding:4px 12px; border-radius:999px; font-size:11px; font-weight:800; display:inline-block; }
        .user-status.active { background:rgba(74,222,128,0.15); color:#4ade80; }
        .user-status.locked { background:rgba(239,68,68,0.15); color:#ef4444; }
        .action-btn { padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.12); background:transparent; color:#b8b8b8; cursor:pointer; font-size:13px; }
        .action-btn:hover { border-color:#38bdf8; color:#38bdf8; }
        .action-btn.danger:hover { border-color:#ef4444; color:#ef4444; }
        .action-btn.success:hover { border-color:#4ade80; color:#4ade80; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <div class="admin-sidebar">
            <h2>ASTRAL ADMIN</h2>
            <a href="/admin">Overview</a>
            <a href="/admin/orders">Orders</a>
            <a href="/admin/products">Products</a>
            <a href="/admin/users" class="active">Users</a>
            <a href="/admin/vouchers">Vouchers</a>
            <a href="/admin/reviews">Reviews</a>
            <a href="/admin/emails">Emails</a>
            <a href="/admin/audit-logs">Audit Logs</a>
            <div style="margin-top:auto;padding-top:32px;">
                <a href="/" style="color:#6b7280;">← Back to site</a>
            </div>
        </div>
        <div class="admin-content">
            <h1>Users</h1>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'status_changed'): ?>
                <div class="alert alert-success">✓ Account lock/unlock status updated!</div>
            <?php endif; ?>

            <div style="background:#151515;border-radius:24px;border:1px solid rgba(255,255,255,0.12);overflow:hidden;">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Info</th>
                                <th>Tier</th>
                                <th>Total Spent</th>
                                <th>Registration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $user): ?>
                                <tr>
                                    <td style="color:#6b7280;">#<?= $user['id'] ?></td>
                                    <td>
                                        <div class="fw-bold" style="color:#38bdf8;"><?= htmlspecialchars($user['name']) ?></div>
                                        <span class="text-muted"><?= htmlspecialchars($user['email']) ?></span>
                                    </td>
                                    <td>
                                        <span class="tier-badge <?= $user['tier'] ?>"><?= strtoupper($user['tier']) ?></span>
                                    </td>
                                    <td class="fw-bold" style="color:#4ade80;"><?= number_format($user['total_spent'], 0, ',', '.') ?> VND</td>
                                    <td style="color:#6b7280;font-size:13px;"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <span class="user-status <?= $user['is_locked'] ? 'locked' : 'active' ?>"><?= $user['is_locked'] ? 'Locked' : 'Active' ?></span>
                                    </td>
                                    <td>
                                        <form action="/admin/users/toggle-lock" method="POST" onsubmit="return confirm('Are you sure you want to <?= $user['is_locked'] ? 'UNLOCK' : 'LOCK' ?> this account?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="action-btn <?= $user['is_locked'] ? 'success' : 'danger' ?>">
                                                <?= $user['is_locked'] ? 'Unlock' : 'Lock' ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
