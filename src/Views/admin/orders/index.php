<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | Astral Cloud Admin</title>
    <link rel="stylesheet" href="/css/base.css">
    <style>
        .order-status { padding:4px 12px; border-radius:999px; font-size:11px; font-weight:800; display:inline-block; }
        .order-status.pending { background:rgba(251,191,36,0.15); color:#fbbf24; }
        .order-status.confirmed { background:rgba(56,189,248,0.15); color:#38bdf8; }
        .order-status.provisioning { background:rgba(56,189,248,0.15); color:#38bdf8; }
        .order-status.active,.order-status.success { background:rgba(74,222,128,0.15); color:#4ade80; }
        .order-status.cancelled { background:rgba(239,68,68,0.15); color:#ef4444; }
        .status-select { background:#0b0b0b; color:#fff; border:1px solid rgba(255,255,255,0.14); padding:8px 12px; border-radius:8px; font-size:13px; width:130px; }
        .status-select:focus { outline:none; border-color:rgba(125,211,252,0.5); }
        .actions-wrap { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
        .btn-icon { padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.12); background:transparent; color:#b8b8b8; cursor:pointer; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; }
        .btn-icon:hover { border-color:#38bdf8; color:#38bdf8; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <div class="admin-sidebar">
            <h2>ASTRAL ADMIN</h2>
            <a href="/admin">Overview</a>
            <a href="/admin/orders" class="active">Orders</a>
            <a href="/admin/products">Products</a>
            <a href="/admin/users">Users</a>
            <a href="/admin/vouchers">Vouchers</a>
            <a href="/admin/reviews">Reviews</a>
            <a href="/admin/emails">Emails</a>
            <a href="/admin/audit-logs">Audit Logs</a>
            <div style="margin-top:auto;padding-top:32px;">
                <a href="/" style="color:#6b7280;">← Back to site</a>
            </div>
        </div>
        <div class="admin-content">
            <h1>Orders</h1>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
                <div class="alert alert-success">✓ Order status updated successfully!</div>
            <?php endif; ?>

            <?php if (isset($_GET['err'])): ?>
                <div class="alert alert-danger">
                    <?php if ($_GET['err'] === 'invalid_transition'): ?>
                        ✗ Cannot change status in that direction. Orders can only move forward or be cancelled.
                    <?php elseif ($_GET['err'] === 'cancel_failed'): ?>
                        ✗ Failed to cancel the order. Please try again.
                    <?php elseif ($_GET['err'] === 'not_found'): ?>
                        ✗ Order not found.
                    <?php else: ?>
                        ✗ An error occurred.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div style="background:#151515;border-radius:24px;border:1px solid rgba(255,255,255,0.12);overflow:hidden;">
                <div class="table-responsive">
                    <table class="admin-table">
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
                                    <td class="fw-bold" style="color:#38bdf8;">#<?= htmlspecialchars($order['order_id']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($order['customer_name']) ?><br>
                                        <span class="text-muted"><?= htmlspecialchars($order['customer_email']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($order['product_name']) ?></td>
                                    <td style="color:#6b7280;font-size:13px;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td class="fw-bold" style="color:#38bdf8;"><?= number_format($order['total_price'], 0, ',', '.') ?> VND</td>
                                    <td>
                                        <?php $s = $order['order_status']; ?>
                                        <span class="order-status <?= $s ?>"><?= strtoupper($s) ?></span>
                                    </td>
                                    <td>
                                        <form action="/admin/orders/update" method="POST" class="actions-wrap">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
                                            <select name="status" class="status-select">
                                                <option value="pending" <?= $s == 'pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="confirmed" <?= $s == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="provisioning" <?= $s == 'provisioning' ? 'selected' : '' ?>>Provisioning</option>
                                                <option value="active" <?= $s == 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="success" <?= $s == 'success' ? 'selected' : '' ?>>Success</option>
                                                <option value="cancelled" <?= $s == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" class="btn-icon">Save</button>
                                            <a href="/admin/orders/invoice?id=<?= $order['order_id'] ?>" class="btn-icon" target="_blank">Invoice</a>
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
