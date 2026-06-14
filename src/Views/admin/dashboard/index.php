<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Overview | Astral Cloud Admin</title>
    <link rel="stylesheet" href="/css/base.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-wrap { background:#151515; border-radius:24px; padding:32px; border:1px solid rgba(255,255,255,0.12); }
        .admin-list { list-style:none; padding:0; margin:0; }
        .admin-list li { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid rgba(255,255,255,0.06); }
        .admin-list li:last-child { border-bottom:none; }
        .admin-list .count-badge { padding:4px 12px; border-radius:999px; font-size:12px; font-weight:800; }
        .admin-list .count-badge.good { background:rgba(74,222,128,0.15); color:#4ade80; }
        .admin-list .count-badge.bad { background:rgba(255,255,255,0.06); color:#6b7280; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <div class="admin-sidebar">
            <h2>ASTRAL ADMIN</h2>
            <a href="/admin" class="active">Overview</a>
            <a href="/admin/orders">Orders</a>
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
            <h1>Overview</h1>

            <div class="admin-cards" style="margin-bottom:32px;">
                <div class="admin-card">
                    <h3>Total Revenue</h3>
                    <p style="color:#38bdf8;"><?= number_format($stats['total_revenue'], 0, ',', '.') ?> VND</p>
                </div>
                <div class="admin-card">
                    <h3>Total Orders</h3>
                    <p style="color:#4ade80;"><?= $stats['total_orders'] ?></p>
                </div>
                <div class="admin-card">
                    <h3>Customers</h3>
                    <p style="color:#fbbf24;"><?= $stats['total_users'] ?></p>
                </div>
            </div>

            <div class="d-flex g-4" style="gap:24px;flex-wrap:wrap;">
                <div class="chart-wrap" style="flex:2;min-width:400px;">
                    <h3 style="margin-bottom:24px;color:#38bdf8;font-size:18px;">Revenue Chart</h3>
                    <canvas id="revenueChart" height="100"></canvas>
                </div>

                <div style="flex:1;min-width:280px;display:flex;flex-direction:column;gap:20px;">
                    <div style="background:#151515;border-radius:24px;padding:24px;border:1px solid rgba(255,255,255,0.12);">
                        <h3 style="color:#4ade80;font-size:15px;margin-bottom:16px;">▲ Top Selling</h3>
                        <ul class="admin-list">
                            <?php foreach ($topProducts as $tp): ?>
                                <li><span><?= htmlspecialchars($tp['product_name']) ?></span><span class="count-badge good"><?= $tp['total_sold'] ?> sold</span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div style="background:#151515;border-radius:24px;padding:24px;border:1px solid rgba(255,255,255,0.12);">
                        <h3 style="color:#ef4444;font-size:15px;margin-bottom:16px;">▼ Slowest Selling</h3>
                        <ul class="admin-list">
                            <?php foreach ($worstProducts as $wp): ?>
                                <li><span><?= htmlspecialchars($wp['product_name']) ?></span><span class="count-badge bad"><?= $wp['total_sold'] ?> sold</span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const labels = <?= (isset($chartLabelsJson) && $chartLabelsJson) ? $chartLabelsJson : json_encode([]) ?>;
        const dataPoints = <?= (isset($chartDataJson) && $chartDataJson) ? $chartDataJson : json_encode([]) ?>;
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(56,189,248,0.5)');
        gradient.addColorStop(1, 'rgba(56,189,248,0)');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (VND)',
                    data: dataPoints,
                    borderColor: '#38bdf8',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#38bdf8',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#6b7280' } },
                    x: { grid: { color: 'rgba(255,255,255,0.06)' }, ticks: { color: '#6b7280' } }
                }
            }
        });
    </script>
</body>
</html>
