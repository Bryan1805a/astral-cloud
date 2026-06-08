<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Management | Astral Cloud Admin</title>
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
        <h3 class="fw-bold text-info mb-4">Review Moderation</h3>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show">
                Review visibility status updated!
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="glass-panel p-4">
            <div class="table-responsive">
                <table class="table table-glass table-hover">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>VPS Plan (Product)</th>
                            <th>Rating</th>
                            <th class="comment-max-width">Comment</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($reviews)): ?>
                            <tr><td colspan="7" class="text-center text-secondary py-4">No reviews yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $r): ?>
                                <tr>
                                    <td class="fw-bold text-cyan"><?= htmlspecialchars($r['customer_name']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($r['product_name']) ?></span></td>
                                    <td class="star-rating">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="bi <?= $i <= $r['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td><?= nl2br(htmlspecialchars($r['comment'])) ?></td>
                                    <td class="text-secondary small"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                                    <td>
                                        <?= $r['is_visible'] ? '<span class="badge bg-success">Visible</span>' : '<span class="badge bg-danger">Hidden</span>' ?>
                                    </td>
                                    <td>
                                        <form action="/admin/reviews/toggle" method="POST" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="btn btn-sm <?= $r['is_visible'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="Toggle visibility">
                                                <i class="bi <?= $r['is_visible'] ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                            </button>
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
</body>
</html>