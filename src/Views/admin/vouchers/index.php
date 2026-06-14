<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promotion Management | Astral Cloud Admin</title>
    <link rel="stylesheet" href="/css/base.css">
    <style>
        .header-row { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:32px; }
        .voucher-code { font-weight:800; font-size:18px; color:#fbbf24; font-family:'Courier New',monospace; }
        .v-status { padding:4px 12px; border-radius:999px; font-size:11px; font-weight:800; display:inline-block; }
        .v-status.active { background:rgba(74,222,128,0.15); color:#4ade80; }
        .v-status.inactive { background:rgba(255,255,255,0.08); color:#6b7280; }
        .v-expired { color:#ef4444; font-weight:700; font-size:13px; }
        .action-btn { padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.12); background:transparent; color:#b8b8b8; cursor:pointer; font-size:13px; }
        .action-btn:hover { border-color:#38bdf8; color:#38bdf8; }
        .action-btn.warning:hover { border-color:#fbbf24; color:#fbbf24; }
        .action-btn.success:hover { border-color:#4ade80; color:#4ade80; }
        .tier-tag { padding:3px 10px; border-radius:999px; font-size:11px; font-weight:800; display:inline-block; }
        .tier-tag.all { background:rgba(255,255,255,0.08); color:#6b7280; }
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
            <a href="/admin/vouchers" class="active">Vouchers</a>
            <a href="/admin/reviews">Reviews</a>
            <a href="/admin/emails">Emails</a>
            <a href="/admin/audit-logs">Audit Logs</a>
            <div style="margin-top:auto;padding-top:32px;">
                <a href="/" style="color:#6b7280;">← Back to site</a>
            </div>
        </div>
        <div class="admin-content">
            <h1>Vouchers</h1>

            <div class="header-row">
                <div></div>
                <button onclick="showModal('addVoucherModal')" class="admin-btn">+ Create New Code</button>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success">✓ Voucher updated successfully!</div>
            <?php endif; ?>

            <div style="background:#151515;border-radius:24px;border:1px solid rgba(255,255,255,0.12);overflow:hidden;">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Condition</th>
                                <th>Qty / Used</th>
                                <th>Tier</th>
                                <th>Expiry</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vouchers as $v): ?>
                                <tr>
                                    <td class="voucher-code"><?= htmlspecialchars($v['code']) ?></td>
                                    <td class="fw-bold" style="color:#38bdf8;">
                                        <?= htmlspecialchars($v['discount_type'] === 'percent' ? $v['discount_value'].'%' : number_format($v['discount_value'], 0, ',', '.').'VND') ?>
                                    </td>
                                    <td style="font-size:13px;">
                                        Min: <?= number_format($v['min_order_value'], 0, ',', '.') ?> VND<br>
                                        <span class="text-muted">Max: <?= $v['max_discount'] ? number_format($v['max_discount'], 0, ',', '.').' VND' : '∞' ?></span>
                                    </td>
                                    <td>
                                        <span style="<?= $v['used_count'] >= $v['quantity'] ? 'color:#ef4444;' : 'color:#4ade80;' ?>font-weight:700;">
                                            <?= $v['used_count'] ?> / <?= $v['quantity'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $t = $v['applicable_tier'];
                                            echo '<span class="tier-tag '.$t.'">'.ucfirst($t).'</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (strtotime($v['expiry_date']) < strtotime('today')): ?>
                                            <span class="v-expired">Expired</span>
                                        <?php else: ?>
                                            <?= date('d/m/Y', strtotime($v['expiry_date'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="v-status <?= $v['is_active'] ? 'active' : 'inactive' ?>"><?= $v['is_active'] ? 'Active' : 'Inactive' ?></span>
                                    </td>
                                    <td>
                                        <form action="/admin/vouchers/toggle" method="POST">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                            <button type="submit" class="action-btn <?= $v['is_active'] ? 'warning' : 'success' ?>">
                                                <?= $v['is_active'] ? 'Lock' : 'Unlock' ?>
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

    <!-- Add Voucher Modal -->
    <div id="addVoucherModal" class="modal-overlay" onclick="if(event.target===this)hideModal('addVoucherModal')">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Promo Code</h5>
                    <button type="button" style="background:none;border:none;color:#b8b8b8;font-size:28px;cursor:pointer;line-height:1;" onclick="hideModal('addVoucherModal')">&times;</button>
                </div>
                <form action="/admin/vouchers/store" method="POST">
                    <?= csrfField() ?>
                    <div class="modal-body row g-3">
                        <div class="col-md-6">
                            <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Code</label>
                            <input type="text" name="code" class="form-control" required placeholder="e.g. NEWYEAR2026" style="text-transform:uppercase;">
                        </div>
                        <div class="col-md-6">
                            <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Description</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Discount Type</label>
                            <select name="discount_type" class="form-select">
                                <option value="percent">Percent (%)</option>
                                <option value="fixed">Fixed (VND)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Discount Value</label>
                            <input type="number" name="discount_value" step="0.01" class="form-control" required placeholder="e.g. 15">
                        </div>
                        <div class="col-md-4">
                            <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Max Discount</label>
                            <input type="number" name="max_discount" class="form-control" placeholder="Leave empty = unlimited">
                        </div>
                        <div class="col-md-4">
                            <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Min Order (VND)</label>
                            <input type="number" name="min_order_value" class="form-control" required value="0">
                        </div>
                        <div class="col-md-4">
                            <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Applicable Tier</label>
                            <select name="applicable_tier" class="form-select">
                                <option value="all">All Customers</option>
                                <option value="silver">Silver & above</option>
                                <option value="gold">VIP Gold & Diamond</option>
                                <option value="diamond">Diamond Exclusive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Quantity</label>
                            <input type="number" name="quantity" class="form-control" required value="100">
                        </div>
                        <div class="col-md-6">
                            <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 d-flex" style="align-items:flex-end;">
                            <label style="display:flex;align-items:center;gap:10px;color:#b8b8b8;font-size:14px;">
                                <input type="checkbox" name="is_active" class="form-check-input" checked style="width:40px;height:20px;">
                                Activate immediately
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideModal('addVoucherModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Promo Code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showModal(id) { document.getElementById(id).classList.add('show'); }
        function hideModal(id) { document.getElementById(id).classList.remove('show'); }
    </script>
</body>
</html>
