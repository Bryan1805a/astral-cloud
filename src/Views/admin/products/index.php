<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Management | Astral Cloud Admin</title>
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 border-bottom border-secondary mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-warning" href="/admin/orders"><i class="bi bi-shield-lock-fill"></i> ASTRAL ADMIN</a>
            <div class="navbar-nav">
                <a class="nav-link" href="/admin/orders">Orders</a>
                <a class="nav-link active text-info" href="/admin/products">Products</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-info">VPS Package Management</h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle"></i> Add New VPS
            </button>
        </div>

        <div class="glass-panel p-4">
            <table class="table table-glass table-hover">
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
                            <td>#<?= $p['id'] ?></td>
                            <td class="fw-bold text-cyan"><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['cpu']) ?> / <?= htmlspecialchars($p['ram']) ?> / <?= htmlspecialchars($p['storage']) ?></td>
                            <td><?= htmlspecialchars($p['bandwidth']) ?></td>
                            <td class="fw-bold text-info"><?= number_format($p['price'], 0, ',', '.') ?>đ</td>
                            <td>
                                <?php if($p['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Hidden</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="/admin/products/toggle" method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm <?= $p['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                        <i class="bi <?= $p['is_active'] ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content glass-modal">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-info">Add New VPS Package</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="/admin/products/store" method="POST">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">VPS Package Name</label>
                                <input type="text" name="name" class="form-control bg-dark text-light border-secondary" required placeholder="E.g.: VPS Enterprise">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price (VND/Month)</label>
                                <input type="number" name="price" class="form-control bg-dark text-light border-secondary" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CPU</label>
                                <input type="text" name="cpu" class="form-control bg-dark text-light border-secondary" required placeholder="E.g.: 4 vCPU">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RAM</label>
                                <input type="text" name="ram" class="form-control bg-dark text-light border-secondary" required placeholder="E.g.: 8 GB">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Storage</label>
                                <input type="text" name="storage" class="form-control bg-dark text-light border-secondary" required placeholder="E.g.: 100 GB NVMe">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bandwidth</label>
                                <input type="text" name="bandwidth" class="form-control bg-dark text-light border-secondary" required placeholder="E.g.: 1 Gbps">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch fs-5 mb-1">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="flexSwitchCheckChecked" checked>
                                    <label class="form-check-label fs-6 ms-2" for="flexSwitchCheckChecked">Enable immediately</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Short Description</label>
                                <textarea name="description" class="form-control bg-dark text-light border-secondary" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>