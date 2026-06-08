<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management | Astral Cloud Admin</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-info">Order Management (Orders)</h3>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show">
                Order status updated successfully!
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="glass-panel p-4">
            <div class="table-responsive">
                <table class="table table-glass table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>VPS Plan</th>
                            <th>Order Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="fw-bold text-cyan">#<?= htmlspecialchars($order['order_id']) ?></td>
                                <td>
                                    <?= htmlspecialchars($order['customer_name']) ?><br>
                                    <small class="text-secondary"><?= htmlspecialchars($order['customer_email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($order['product_name']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                <td class="fw-bold text-info"><?= number_format($order['total_price'], 0, ',', '.') ?>đ</td>
                                <td>
                                    <?php
                                        $bgClass = 'bg-secondary';
                                        if ($order['order_status'] === 'pending') $bgClass = 'bg-warning text-dark';
                                        if ($order['order_status'] === 'confirmed') $bgClass = 'bg-info text-dark';
                                        if ($order['order_status'] === 'provisioning') $bgClass = 'bg-info text-dark';
                                        if ($order['order_status'] === 'active' || $order['order_status'] === 'success') $bgClass = 'bg-success';
                                        if ($order['order_status'] === 'cancelled') $bgClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $bgClass ?>"><?= strtoupper($order['order_status']) ?></span>
                                </td>
                                <td>
                                    <form action="/admin/orders/update" method="POST" class="d-flex gap-2">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
                                        <select name="status" class="form-select form-select-sm bg-dark text-light border-secondary status-select-width">
                                            <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="confirmed" <?= $order['order_status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="provisioning" <?= $order['order_status'] == 'provisioning' ? 'selected' : '' ?>>Provisioning</option>
                                            <option value="active" <?= $order['order_status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="success" <?= $order['order_status'] == 'success' ? 'selected' : '' ?>>Success</option>
                                            <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-warning">Save</button>
                                        <a href="/admin/orders/invoice?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-outline-info" title="Download Invoice" target="_blank">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
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