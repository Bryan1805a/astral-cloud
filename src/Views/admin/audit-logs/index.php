<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs | Astral Cloud Admin</title>
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
                    <a class="nav-link" href="/admin">Overview</a>
                    <a class="nav-link" href="/admin/orders">Orders</a>
                    <a class="nav-link" href="/admin/products">Products</a>
                    <a class="nav-link" href="/admin/users">Users</a>
                    <a class="nav-link" href="/admin/vouchers">Vouchers</a>
                    <a class="nav-link" href="/admin/reviews">Reviews</a>
                    <a class="nav-link" href="/admin/emails">Emails</a>
                    <a class="nav-link active text-info" href="/admin/audit-logs">Audit Logs</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <h3 class="fw-bold text-info mb-4"><i class="bi bi-journal-text"></i> Audit Logs</h3>

        <form method="GET" action="/admin/audit-logs" class="row g-3 mb-4">
            <div class="col-md-4">
                <select name="action" class="form-select bg-dark text-light border-secondary">
                    <option value="">All Actions</option>
                    <?php foreach ($actions as $a): ?>
                        <option value="<?= htmlspecialchars($a) ?>" <?= $actionFilter === $a ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select name="entity" class="form-select bg-dark text-light border-secondary">
                    <option value="">All Entity Types</option>
                    <?php foreach ($entityTypes as $e): ?>
                        <option value="<?= htmlspecialchars($e) ?>" <?= $entityFilter === $e ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($e)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-info"><i class="bi bi-funnel"></i> Filter</button>
                <a href="/admin/audit-logs" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>

        <div class="glass-panel p-4">
            <div class="table-responsive">
                <table class="table table-glass table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>ID</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="7" class="text-center text-secondary py-4">No audit logs yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="text-secondary small"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($log['user_name'] ?? 'System') ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($log['action']) ?></span></td>
                                    <td><?= htmlspecialchars(ucfirst($log['entity_type'])) ?></td>
                                    <td><?= $log['entity_id'] ? '#' . $log['entity_id'] : '-' ?></td>
                                    <td><?= htmlspecialchars($log['description']) ?></td>
                                    <td class="text-secondary small font-monospace"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link bg-dark border-secondary text-light" href="/admin/audit-logs?page=<?= $i ?>&action=<?= urlencode($actionFilter ?? '') ?>&entity=<?= urlencode($entityFilter ?? '') ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
