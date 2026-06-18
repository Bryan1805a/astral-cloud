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
            <a href="/plans">FEATURES</a>
            <a href="/plans#plans">VPS PLANS</a>
            <a href="/docs">DOCS</a>
            <a href="/blog">BLOG</a>
        </div>
        <div class="nav-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $notifCount = \App\Models\AdminEmail::getUnreadCount($_SESSION['user_id']);
                ?>
                <a href="/inbox" class="nav-notif-btn" title="Notifications">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <?php if ($notifCount > 0): ?>
                        <span class="notif-badge"><?= $notifCount > 99 ? '99+' : $notifCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="/cart" class="btn-live" style="display:flex;align-items:center;gap:6px;">
                    Cart <span id="cart-badge" style="background:#ef4444;color:#fff;border-radius:999px;padding:2px 8px;font-size:11px;display:none;">0</span>
                </a>
                <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff'): ?>
                    <a href="/admin" class="btn-live">Admin</a>
                <?php endif; ?>
                <div class="user-dropdown" style="position:relative;">
                    <button class="user-dropdown-btn" style="display:flex;align-items:center;gap:8px;background:transparent;border:1px solid rgba(255,255,255,0.12);color:#fff;padding:8px 16px;border-radius:999px;cursor:pointer;font-family:inherit;font-size:13px;font-weight:600;">
                        <?= htmlspecialchars($_SESSION['user_name']) ?>
                        <span style="color:#38bdf8;font-size:11px;">[<?= htmlspecialchars($_SESSION['user_tier'] ?? 'Silver') ?>]</span>
                        <svg width="10" height="6" viewBox="0 0 10 6" style="margin-left:4px;"><path d="M1 1l4 4 4-4" stroke="#6b7280" stroke-width="1.5" fill="none"/></svg>
                    </button>
                    <div class="user-dropdown-menu" style="display:none;position:absolute;right:0;top:110%;min-width:180px;background:#151515;border:1px solid rgba(255,255,255,0.12);border-radius:16px;padding:8px;z-index:10000;backdrop-filter:blur(18px);">
                        <a href="/profile" style="display:block;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;color:#b8b8b8;transition:all 0.15s;">My Profile</a>
                        <a href="/orders" style="display:block;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;color:#b8b8b8;transition:all 0.15s;">My Orders</a>
                        <a href="/inbox" style="display:block;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;color:#b8b8b8;transition:all 0.15s;display:flex;align-items:center;justify-content:space-between;">
                            Inbox
                            <?php
                            $unreadCount = \App\Models\AdminEmail::getUnreadCount($_SESSION['user_id']);
                            if ($unreadCount > 0): ?>
                                <span style="background:#ef4444;color:#fff;border-radius:999px;padding:1px 8px;font-size:11px;font-weight:700;"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                            <?php endif; ?>
                        </a>
                        <div style="height:1px;background:rgba(255,255,255,0.08);margin:4px 8px;"></div>
                        <a href="/logout" style="display:block;padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;color:#ef4444;transition:all 0.15s;">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/register" class="btn-start">Start now</a>
                <a href="/login" class="btn-live">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
.user-dropdown-menu a:hover { background: rgba(255,255,255,0.06); color: #fff; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var dd = document.querySelector('.user-dropdown');
    if (!dd) return;
    var btn = dd.querySelector('.user-dropdown-btn');
    var menu = dd.querySelector('.user-dropdown-menu');
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
    document.addEventListener('click', function() {
        menu.style.display = 'none';
    });
});
</script>

<div id="toast-container" class="toast-container" style="position:fixed;top:80px;right:20px;z-index:1080;display:flex;flex-direction:column;gap:8px;"></div>
