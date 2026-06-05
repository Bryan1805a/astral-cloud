<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Promotion Management | Astral Cloud Admin</title>
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
        .table-glass th, .table-glass td {
            background: transparent;
            color: #f8fafc;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            vertical-align: middle;
        }
        .modal-content.glass-modal {
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(56, 189, 248, 0.3);
            color: white;
        }
        .tier-silver { background-color: #94a3b8; color: #0f172a; }
        .tier-gold { background-color: #fbbf24; color: #0f172a; }
        .tier-diamond { background-color: #22d3ee; color: #0f172a; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 border-bottom border-secondary mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-warning" href="/admin"><i class="bi bi-shield-lock-fill"></i> ASTRAL ADMIN</a>
            <div class="navbar-nav">
                <a class="nav-link" href="/admin">Statistics</a>
                <a class="nav-link" href="/admin/orders">Orders</a>
                <a class="nav-link" href="/admin/products">Products</a>
                <a class="nav-link" href="/admin/users">Customers</a>
                <a class="nav-link active text-info" href="/admin/vouchers">Promotions</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-info">Voucher & Promotion Management</h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVoucherModal">
                <i class="bi bi-plus-circle"></i> Create New Code
            </button>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show">
                Voucher updated successfully!
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="glass-panel p-4">
            <table class="table table-glass table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Condition (Min / Max)</th>
                        <th>Qty / Used</th>
                        <th>Applicable Tier</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vouchers as $v): ?>
                        <tr>
                            <td class="fw-bold text-warning fs-5"><?= htmlspecialchars($v['code']) ?></td>
                            <td class="fw-bold text-info">
                                <?= htmlspecialchars($v['discount_type'] === 'percent' ? $v['discount_value'].'%' : number_format($v['discount_value'], 0, ',', '.').'VND') ?>
                            </td>
                            <td>
                                Min order: <?= number_format($v['min_order_value'], 0, ',', '.') ?>VND<br>
                                <small class="text-secondary">Max: <?= $v['max_discount'] ? number_format($v['max_discount'], 0, ',', '.').'VND' : 'Unlimited' ?></small>
                            </td>
                            <td>
                                <span class="<?= $v['used_count'] >= $v['quantity'] ? 'text-danger' : 'text-success' ?>">
                                    <?= $v['used_count'] ?> / <?= $v['quantity'] ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    if ($v['applicable_tier'] === 'all') echo '<span class="badge bg-secondary">All</span>';
                                    if ($v['applicable_tier'] === 'silver') echo '<span class="badge tier-silver">Silver</span>';
                                    if ($v['applicable_tier'] === 'gold') echo '<span class="badge tier-gold">Gold</span>';
                                    if ($v['applicable_tier'] === 'diamond') echo '<span class="badge tier-diamond">Diamond</span>';
                                ?>
                            </td>
                            <td>
                                <?php if (strtotime($v['expiry_date']) < strtotime('today')): ?>
                                    <span class="text-danger fw-bold"><i class="bi bi-x-circle"></i> Expired</span>
                                <?php else: ?>
                                    <?= date('d/m/Y', strtotime($v['expiry_date'])) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $v['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                            </td>
                            <td>
                                <form action="/admin/vouchers/toggle" method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                    <button type="submit" class="btn btn-sm <?= $v['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                        <i class="bi <?= $v['is_active'] ? 'bi-power' : 'bi-check2-circle' ?>"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addVoucherModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content glass-modal">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-info">Create Promo Code (Voucher)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="/admin/vouchers/store" method="POST">
                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Code (Auto-uppercased)</label>
                            <input type="text" name="code" class="form-control bg-dark text-light border-secondary text-uppercase" required placeholder="e.g. NEWYEAR2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control bg-dark text-light border-secondary">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Discount Type</label>
                            <select name="discount_type" class="form-select bg-dark text-light border-secondary">
                                <option value="percent">Percent (%)</option>
                                <option value="fixed">Fixed (VND)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Discount Value</label>
                            <input type="number" name="discount_value" step="0.01" class="form-control bg-dark text-light border-secondary" required placeholder="e.g. 15">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Max Discount (if %, leave empty = unlimited)</label>
                            <input type="number" name="max_discount" class="form-control bg-dark text-light border-secondary">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Min Order (VND)</label>
                            <input type="number" name="min_order_value" class="form-control bg-dark text-light border-secondary" required value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Applicable Tier</label>
                            <select name="applicable_tier" class="form-select bg-dark text-light border-secondary">
                                <option value="all">All Customers</option>
                                <option value="silver">Silver & above</option>
                                <option value="gold">VIP Gold & Diamond only</option>
                                <option value="diamond">Diamond Exclusive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Limited Quantity</label>
                            <input type="number" name="quantity" class="form-control bg-dark text-light border-secondary" required value="100">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control bg-dark text-light border-secondary" required>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch fs-5 mb-1">
                                <input class="form-check-input" type="checkbox" name="is_active" checked>
                                <label class="form-check-label fs-6 ms-2">Activate immediately</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Create Promo Code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>