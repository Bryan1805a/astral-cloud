<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Management | Astral Cloud Admin</title>
    <link rel="stylesheet" href="/css/base.css">
    <style>
        .r-status { padding:4px 12px; border-radius:999px; font-size:11px; font-weight:800; display:inline-block; }
        .r-status.visible { background:rgba(74,222,128,0.15); color:#4ade80; }
        .r-status.hidden { background:rgba(239,68,68,0.15); color:#ef4444; }
        .star { color:#fbbf24; font-size:18px; }
        .star.empty { color:#6b7280; }
        .action-btn { padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.12); background:transparent; color:#b8b8b8; cursor:pointer; font-size:13px; }
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
            <a href="/admin/users">Users</a>
            <a href="/admin/vouchers">Vouchers</a>
            <a href="/admin/reviews" class="active">Reviews</a>
            <a href="/admin/emails">Emails</a>
            <a href="/admin/audit-logs">Audit Logs</a>
            <div style="margin-top:auto;padding-top:32px;">
                <a href="/" style="color:#6b7280;">← Back to site</a>
            </div>
        </div>
        <div class="admin-content">
            <h1>Reviews</h1>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success">✓ Review visibility status updated!</div>
            <?php endif; ?>

            <div style="background:#151515;border-radius:24px;border:1px solid rgba(255,255,255,0.12);overflow:hidden;">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>VPS Plan</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($reviews)): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No reviews yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($reviews as $r): ?>
                                    <tr>
                                        <td class="fw-bold" style="color:#38bdf8;"><?= htmlspecialchars($r['customer_name']) ?></td>
                                        <td><span style="padding:3px 10px;border-radius:999px;background:rgba(255,255,255,0.08);color:#6b7280;font-size:12px;font-weight:700;"><?= htmlspecialchars($r['product_name']) ?></span></td>
                                        <td class="star-rating">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <span class="star <?= $i > $r['rating'] ? 'empty' : '' ?>">★</span>
                                            <?php endfor; ?>
                                        </td>
                                        <td style="max-width:300px;"><?= nl2br(htmlspecialchars($r['comment'])) ?></td>
                                        <td style="color:#6b7280;font-size:13px;"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                                        <td>
                                            <span class="r-status <?= $r['is_visible'] ? 'visible' : 'hidden' ?>"><?= $r['is_visible'] ? 'Visible' : 'Hidden' ?></span>
                                        </td>
                                        <td>
                                            <form action="/admin/reviews/toggle" method="POST">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                <button type="submit" class="action-btn <?= $r['is_visible'] ? 'danger' : 'success' ?>"><?= $r['is_visible'] ? 'Hide' : 'Unhide' ?></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
