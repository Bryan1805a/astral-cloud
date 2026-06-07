<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Astral Cloud') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/css/base.css">
    <?php if (!empty($css)): ?>
        <?php foreach ((array)$css as $file): ?>
            <link rel="stylesheet" href="/css/<?= $file ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 border-bottom border-secondary mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold text-info" href="/">
                <i class="bi bi-cloud-lightning-fill"></i> ASTRAL CLOUD
            </a>
            <div class="d-flex">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="navbar-text me-3">
                        Hello, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                        (Rank: <?= htmlspecialchars($_SESSION['user_tier'] ?? 'Silver') ?>)
                    </span>
                    <a href="/cart" class="btn btn-outline-info btn-sm me-2 position-relative">
                        <i class="bi bi-cart3"></i> Cart
                        <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;font-size:0.6rem;">0</span>
                    </a>
                    <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff'): ?>
                        <a href="/admin" class="btn btn-outline-warning btn-sm me-2">Admin Panel</a>
                    <?php endif; ?>
                    <a href="/logout" class="btn btn-outline-danger btn-sm">Log out</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline-info btn-sm me-2">Log in</a>
                    <a href="/register" class="btn btn-primary btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container">
