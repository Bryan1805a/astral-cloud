<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs | Astral Cloud Admin</title>
    <link rel="stylesheet" href="/css/base.css">
    <style>
        .filter-bar { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; margin-bottom:32px; }
        .filter-bar select { flex:1; min-width:180px; }
        .log-action { padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700; background:rgba(255,255,255,0.08); color:#b8b8b8; display:inline-block; }
        .mono { font-family:'Courier New',monospace; font-size:13px; color:#6b7280; }
        .filter-btn { padding:10px 20px; border-radius:12px; border:1px solid #38bdf8; background:transparent; color:#38bdf8; font-weight:600; cursor:pointer; }
        .filter-btn:hover { background:#38bdf8; color:#0b0b0b; }
        .filter-clear { padding:10px 20px; border-radius:12px; border:1px solid rgba(255,255,255,0.12); background:transparent; color:#b8b8b8; font-weight:600; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; }
        .filter-clear:hover { border-color:#b8b8b8; }
        .pagination-wrap { display:flex; justify-content:center; gap:4px; margin-top:24px; }
        .page-link { padding:10px 16px; border-radius:10px; background:#0b0b0b; border:1px solid rgba(255,255,255,0.1); color:#fff; text-decoration:none; font-size:14px; }
        .page-link.active { background:#38bdf8; color:#0b0b0b; border-color:#38bdf8; }
        .page-link:hover:not(.active) { background:rgba(255,255,255,0.08); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <div class="admin-sidebar">
            <h2>ASTRAL ADMIN</h2>
            <a href="/admin">Overview</a>
            <a href="/admin/orders">Orders</a>
            <a href="/admin/products">Products</a>
            <a href="/admin/users">Users</a>
            <a href="/admin/vouchers">Vouchers</a>
            <a href="/admin/reviews">Reviews</a>
            <a href="/admin/emails">Emails</a>
            <a href="/admin/audit-logs" class="active">Audit Logs</a>
            <div style="margin-top:auto;padding-top:32px;">
                <a href="/" style="color:#6b7280;">← Back to site</a>
            </div>
        </div>
        <div class="admin-content">
            <h1>Audit Logs</h1>

            <form method="GET" action="/admin/audit-logs" class="filter-bar">
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <?php foreach ($actions as $a): ?>
                        <option value="<?= htmlspecialchars($a) ?>" <?= $actionFilter === $a ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="entity" class="form-select">
                    <option value="">All Entity Types</option>
                    <?php foreach ($entityTypes as $e): ?>
                        <option value="<?= htmlspecialchars($e) ?>" <?= $entityFilter === $e ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($e)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="filter-btn">Filter</button>
                <a href="/admin/audit-logs" class="filter-clear">Clear</a>
            </form>

            <div style="background:#151515;border-radius:24px;border:1px solid rgba(255,255,255,0.12);overflow:hidden;">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Entity</th>
                                <th>ID</th>
                                <th>Description</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No audit logs yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td style="color:#6b7280;font-size:13px;"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($log['user_name'] ?? 'System') ?></td>
                                        <td><span class="log-action"><?= htmlspecialchars($log['action']) ?></span></td>
                                        <td><?= htmlspecialchars(ucfirst($log['entity_type'])) ?></td>
                                        <td><?= $log['entity_id'] ? '#'.$log['entity_id'] : '-' ?></td>
                                        <td style="max-width:300px;font-size:13px;"><?= htmlspecialchars($log['description']) ?></td>
                                        <td class="mono"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination-wrap">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="/admin/audit-logs?page=<?= $i ?>&action=<?= urlencode($actionFilter ?? '') ?>&entity=<?= urlencode($entityFilter ?? '') ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
