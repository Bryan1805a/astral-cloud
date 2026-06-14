<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Astral Cloud') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/base.css">
    <?php if (!empty($css)): ?>
        <?php foreach ((array)$css as $file): ?>
            <link rel="stylesheet" href="/css/<?= $file ?>.css">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<div class="cursor-glow"></div>

<nav class="main-navbar">
    <div class="nav-container">
        <a href="/" class="brand">
            <div class="brand-icon">A</div>
            <span>Astral Cloud</span>
        </a>
        <div class="nav-menu">
            <a href="/">HOME</a>
            <a href="/#about">ABOUT</a>
            <a href="/#features">FEATURES</a>
            <a href="/#plans">VPS PLANS</a>
            <a href="/#blog">BLOG</a>
        </div>
        <div class="nav-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="font-size:13px;font-weight:600;color:#b8b8b8;">
                    Hi, <span style="color:#fff;"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <span style="color:#38bdf8;font-size:11px;">[<?= htmlspecialchars($_SESSION['user_tier'] ?? 'Silver') ?>]</span>
                </span>
                <a href="/cart" class="btn-live" style="display:flex;align-items:center;gap:6px;">
                    Cart <span id="cart-badge" style="background:#ef4444;color:#fff;border-radius:999px;padding:2px 8px;font-size:11px;display:none;">0</span>
                </a>
                <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff'): ?>
                    <a href="/admin" class="btn-live">Admin</a>
                <?php endif; ?>
                <a href="/logout" class="btn-live" style="border-color:#ef4444;color:#ef4444;">Logout</a>
            <?php else: ?>
                <a href="/register" class="btn-start">Start now</a>
                <a href="/login" class="btn-live">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div id="toast-container" class="toast-container" style="position:fixed;top:80px;right:20px;z-index:1080;display:flex;flex-direction:column;gap:8px;"></div>
