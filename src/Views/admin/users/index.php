<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Management | Astral Cloud Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 border-bottom border-secondary mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-warning" href="/admin"><i class="bi bi-shield-lock-fill"></i> ASTRAL ADMIN</a>
            <div class="navbar-nav">
                    <a class="nav-link active text-info" href="/admin">Overview</a>
                    <a class="nav-link" href="/admin/orders">Orders</a>
                    <a class="nav-link" href="/admin/products">Products</a>
                    <a class="nav-link" href="/admin/users">Users</a>
                    <a class="nav-link" href="/admin/vouchers">Vouchers</a>
                    <a class="nav-link" href="/admin/reviews">Reviews</a>
                    <a class="nav-link" href="/admin/emails">Emails</a>
                    <a class="nav-link" href="/admin/audit-logs">Audit Logs</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <h3 class="fw-bold text-info mb-4">Customer Management</h3>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'status_changed'): ?>
            <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show">
                Account lock/unlock status updated!
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="glass-panel p-4">
            <div class="table-responsive">
                <table class="table table-glass table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Info</th>
                            <th>Tier</th>
                            <th>Total Spent</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $user): ?>
                            <tr>
                                <td>#<?= $user['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-cyan"><?= htmlspecialchars($user['name']) ?></div>
                                    <small class="text-secondary"><?= htmlspecialchars($user['email']) ?></small>
                                </td>
                                <td>
                                    <?php 
                                        $tierClass = 'tier-silver';
                                        $tierIcon = 'bi-star';
                                        if ($user['tier'] === 'gold') {
                                            $tierClass = 'tier-gold';
                                            $tierIcon = 'bi-star-half';
                                        } elseif ($user['tier'] === 'diamond') {
                                            $tierClass = 'tier-diamond';
                                            $tierIcon = 'bi-star-fill';
                                        }
                                    ?>
                                    <span class="badge <?= $tierClass ?> px-2 py-1 fs-6">
                                        <i class="bi <?= $tierIcon ?>"></i> <?= strtoupper($user['tier']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-success">
                                    <?= number_format($user['total_spent'], 0, ',', '.') ?>đ
                                </td>
                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php if ($user['is_locked'] == 1): ?>
                                        <span class="badge bg-danger">Locked</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="/admin/users/toggle-lock" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to <?= $user['is_locked'] ? 'UNLOCK' : 'LOCK' ?> this account?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-sm <?= $user['is_locked'] ? 'btn-outline-success' : 'btn-outline-danger' ?>">
                                            <i class="bi <?= $user['is_locked'] ? 'bi-unlock' : 'bi-lock' ?>"></i>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>