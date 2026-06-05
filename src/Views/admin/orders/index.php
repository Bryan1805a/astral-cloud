<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Management | Astral Cloud Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f8fafc; }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
        }
        .table-glass { color: #f8fafc; }
        .table-glass th, .table-glass td {
            background: transparent;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 border-bottom border-secondary mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-warning" href="/admin/orders">
                <i class="bi bi-shield-lock-fill"></i> ASTRAL ADMIN PANEL
            </a>
            <div class="d-flex">
                <a href="/" class="btn btn-outline-light btn-sm me-2">View Site</a>
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
                                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
                                        <select name="status" class="form-select form-select-sm bg-dark text-light border-secondary" style="width: 130px;">
                                            <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="confirmed" <?= $order['order_status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="provisioning" <?= $order['order_status'] == 'provisioning' ? 'selected' : '' ?>>Provisioning</option>
                                            <option value="active" <?= $order['order_status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="success" <?= $order['order_status'] == 'success' ? 'selected' : '' ?>>Success</option>
                                            <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-warning">Save</button>
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