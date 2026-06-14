<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management | Astral Cloud Admin</title>
    <link rel="stylesheet" href="/css/base.css">
    <style>
        .status-badge { padding:4px 12px; border-radius:999px; font-size:11px; font-weight:800; display:inline-block; }
        .status-badge.active { background:rgba(74,222,128,0.15); color:#4ade80; }
        .status-badge.hidden { background:rgba(255,255,255,0.08); color:#6b7280; }
        .action-btn { padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.12); background:transparent; color:#b8b8b8; cursor:pointer; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:4px; }
        .action-btn:hover { border-color:#38bdf8; color:#38bdf8; }
        .action-btn.danger:hover { border-color:#ef4444; color:#ef4444; }
        .action-btn.success:hover { border-color:#4ade80; color:#4ade80; }
        .header-row { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:32px; }
        .toast-msg { margin-bottom:24px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <div class="admin-sidebar">
            <h2>ASTRAL ADMIN</h2>
            <a href="/admin">Overview</a>
            <a href="/admin/orders">Orders</a>
            <a href="/admin/products" class="active">Products</a>
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
            <h1>Products</h1>

            <div class="header-row">
                <div></div>
                <button onclick="showModal('addProductModal')" class="admin-btn">+ Add New VPS</button>
            </div>

            <div style="background:#151515;border-radius:24px;border:1px solid rgba(255,255,255,0.12);overflow:hidden;">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Package Name</th>
                                <th>Specs (CPU / RAM / Disk)</th>
                                <th>Bandwidth</th>
                                <th>Price (VND)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td style="color:#6b7280;">#<?= $p['id'] ?></td>
                                    <td class="fw-bold" style="color:#38bdf8;"><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= htmlspecialchars($p['cpu']) ?> / <?= htmlspecialchars($p['ram']) ?> / <?= htmlspecialchars($p['storage']) ?></td>
                                    <td><?= htmlspecialchars($p['bandwidth']) ?></td>
                                    <td class="fw-bold" style="color:#38bdf8;"><?= number_format($p['price'], 0, ',', '.') ?> VND</td>
                                    <td>
                                        <span class="status-badge <?= $p['is_active'] ? 'active' : 'hidden' ?>"><?= $p['is_active'] ? 'Active' : 'Hidden' ?></span>
                                    </td>
                                    <td>
                                        <button class="action-btn" onclick="editProduct(<?= $p['id'] ?>)">Edit</button>
                                        <form action="/admin/products/toggle" method="POST" style="display:inline;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="action-btn <?= $p['is_active'] ? 'danger' : 'success' ?>"><?= $p['is_active'] ? 'Lock' : 'Unlock' ?></button>
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

    <!-- Add Modal -->
    <div id="addProductModal" class="modal-overlay" onclick="if(event.target===this)hideModal('addProductModal')">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New VPS Package</h5>
                    <button type="button" style="background:none;border:none;color:#b8b8b8;font-size:28px;cursor:pointer;line-height:1;" onclick="hideModal('addProductModal')">&times;</button>
                </div>
                <form action="/admin/products/store" method="POST">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">VPS Package Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="E.g.: VPS Enterprise">
                            </div>
                            <div class="col-md-6">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Price (VND/Month)</label>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">CPU</label>
                                <input type="text" name="cpu" class="form-control" required placeholder="E.g.: 4 vCPU">
                            </div>
                            <div class="col-md-4">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">RAM</label>
                                <input type="text" name="ram" class="form-control" required placeholder="E.g.: 8 GB">
                            </div>
                            <div class="col-md-4">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Storage</label>
                                <input type="text" name="storage" class="form-control" required placeholder="E.g.: 100 GB NVMe">
                            </div>
                            <div class="col-md-6">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Bandwidth</label>
                                <input type="text" name="bandwidth" class="form-control" required placeholder="E.g.: 1 Gbps">
                            </div>
                            <div class="col-md-6 d-flex" style="align-items:flex-end;">
                                <label style="display:flex;align-items:center;gap:10px;color:#b8b8b8;font-size:14px;">
                                    <input type="checkbox" name="is_active" class="form-check-input" checked style="width:40px;height:20px;">
                                    Enable immediately
                                </label>
                            </div>
                            <div class="col-12">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Short Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideModal('addProductModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editProductModal" class="modal-overlay" onclick="if(event.target===this)hideModal('editProductModal')">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit VPS Package</h5>
                    <button type="button" style="background:none;border:none;color:#b8b8b8;font-size:28px;cursor:pointer;line-height:1;" onclick="hideModal('editProductModal')">&times;</button>
                </div>
                <form action="/admin/products/update" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">VPS Package Name</label>
                                <input type="text" name="name" id="edit-name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Price (VND/Month)</label>
                                <input type="number" name="price" id="edit-price" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">CPU</label>
                                <input type="text" name="cpu" id="edit-cpu" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">RAM</label>
                                <input type="text" name="ram" id="edit-ram" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Storage</label>
                                <input type="text" name="storage" id="edit-storage" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Bandwidth</label>
                                <input type="text" name="bandwidth" id="edit-bandwidth" class="form-control" required>
                            </div>
                            <div class="col-md-6 d-flex" style="align-items:flex-end;">
                                <label style="display:flex;align-items:center;gap:10px;color:#b8b8b8;font-size:14px;">
                                    <input type="checkbox" name="is_active" id="edit-is_active" class="form-check-input" style="width:40px;height:20px;">
                                    Active
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Stock</label>
                                <input type="number" name="stock" id="edit-stock" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label style="display:block;margin-bottom:6px;color:#b8b8b8;font-size:13px;">Short Description</label>
                                <textarea name="description" id="edit-description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideModal('editProductModal')">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showModal(id) { document.getElementById(id).classList.add('show'); }
        function hideModal(id) { document.getElementById(id).classList.remove('show'); }

        const products = <?= json_encode($products) ?>;
        function editProduct(id) {
            const p = products.find(x => x.id == id);
            if (!p) return;
            document.getElementById('edit-id').value = p.id;
            document.getElementById('edit-name').value = p.name;
            document.getElementById('edit-description').value = p.description || '';
            document.getElementById('edit-cpu').value = p.cpu;
            document.getElementById('edit-ram').value = p.ram;
            document.getElementById('edit-storage').value = p.storage;
            document.getElementById('edit-bandwidth').value = p.bandwidth;
            document.getElementById('edit-price').value = p.price;
            document.getElementById('edit-stock').value = p.stock;
            document.getElementById('edit-is_active').checked = p.is_active == 1;
            showModal('editProductModal');
        }
    </script>
</body>
</html>
