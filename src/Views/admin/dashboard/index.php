<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Astral Cloud</title>
    <link rel="stylesheet" href="/css/base.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dash-grid { display:grid; grid-template-columns: 1fr 420px; gap:20px; }
        @media (max-width:1100px) { .dash-grid { grid-template-columns: 1fr; } }

        /* -- Stat cards -- */
        .stat-cards { display:grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap:14px; margin-bottom:20px; }
        .stat-card { background:#141414; border:1px solid rgba(255,255,255,0.08); border-radius:16px; padding:18px 20px; position:relative; overflow:hidden; }
        .stat-card .stat-icon { position:absolute; top:12px; right:16px; font-size:28px; opacity:0.12; }
        .stat-card .stat-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#6b7280; margin-bottom:6px; }
        .stat-card .stat-value { font-size:22px; font-weight:800; color:#e2e8f0; }
        .stat-card .stat-sub { font-size:11px; color:#6b7280; margin-top:4px; }
        .stat-card.accent-blue  .stat-value { color:#38bdf8; }
        .stat-card.accent-green .stat-value { color:#4ade80; }
        .stat-card.accent-amber .stat-value { color:#fbbf24; }
        .stat-card.accent-teal  .stat-value { color:#2dd4bf; }

        /* -- Revenue comparison -- */
        .rev-compare { display:flex; align-items:center; gap:16px; background:#141414; border:1px solid rgba(255,255,255,0.08); border-radius:16px; padding:14px 20px; margin-bottom:16px; flex-wrap:wrap; }
        .rev-compare .rev-month { font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px; }
        .rev-compare .rev-amount { font-size:18px; font-weight:800; color:#e2e8f0; }
        .rev-compare .rev-sep { color:#374151; font-size:20px; }
        .rev-compare .rev-change { font-size:13px; font-weight:700; padding:4px 10px; border-radius:6px; }
        .rev-compare .rev-change.up   { color:#4ade80; background:rgba(74,222,128,0.1); }
        .rev-compare .rev-change.down { color:#f87171; background:rgba(248,113,113,0.1); }
        .rev-compare .rev-arrow { font-size:14px; }

        /* -- Panels -- */
        .panel { background:#141414; border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:24px; }
        .panel h3 { font-size:14px; font-weight:700; color:#94a3b8; margin:0 0 16px; text-transform:uppercase; letter-spacing:1px; }

        .chart-wrap { margin-bottom:20px; }
        .chart-wrap canvas { max-height:240px; }

        /* -- Product bars -- */
        .product-bars { margin-top:8px; }
        .product-bar { margin-bottom:10px; }
        .product-bar .bar-label { display:flex; justify-content:space-between; font-size:11px; color:#94a3b8; margin-bottom:4px; }
        .product-bar .bar-label strong { color:#e2e8f0; }
        .product-bar .bar-track { height:7px; background:rgba(255,255,255,0.04); border-radius:4px; overflow:hidden; }
        .product-bar .bar-fill { height:100%; border-radius:4px; transition:width 0.6s ease; }
        .product-bar .bar-fill.c1 { background:linear-gradient(90deg, #38bdf8, #0ea5e9); }
        .product-bar .bar-fill.c2 { background:linear-gradient(90deg, #818cf8, #6366f1); }
        .product-bar .bar-fill.c3 { background:linear-gradient(90deg, #c084fc, #a855f7); }
        .product-bar .bar-fill.c4 { background:linear-gradient(90deg, #f472b6, #ec4899); }
        .product-bar .bar-fill.c5 { background:linear-gradient(90deg, #fb923c, #f97316); }
        .product-bar .bar-fill.c6 { background:linear-gradient(90deg, #4ade80, #22c55e); }

        /* -- Customers table -- */
        .cust-table { width:100%; border-collapse:collapse; font-size:12px; }
        .cust-table th { text-align:left; color:#6b7280; font-size:10px; text-transform:uppercase; letter-spacing:0.5px; padding:0 0 10px; }
        .cust-table td { padding:8px 0; border-top:1px solid rgba(255,255,255,0.04); color:#cbd5e1; }
        .cust-table .cust-name { color:#e2e8f0; font-weight:600; }
        .tier-badge { display:inline-block; padding:2px 8px; border-radius:999px; font-size:10px; font-weight:700; }
        .tier-badge.silver  { background:rgba(148,163,184,0.15); color:#94a3b8; }
        .tier-badge.gold    { background:rgba(251,191,36,0.15); color:#fbbf24; }
        .tier-badge.diamond { background:rgba(56,189,248,0.15); color:#38bdf8; }

        /* -- Service health (compact) -- */
        .svc-mini { display:flex; gap:6px; align-items:center; }
        .svc-mini .svc-dot { display:flex; align-items:center; gap:5px; font-size:11px; color:#94a3b8; }
        .svc-mini .svc-dot span { font-weight:700; color:#e2e8f0; }
        .svc-dot-indicator { display:inline-block; width:8px; height:8px; border-radius:50%; }
        .svc-dot-indicator.green  { background:#4ade80; box-shadow:0 0 6px rgba(74,222,128,0.5); }
        .svc-dot-indicator.yellow { background:#fbbf24; box-shadow:0 0 6px rgba(251,191,36,0.5); }
        .svc-dot-indicator.gray   { background:#374151; }

        /* -- Sys status -- */
        .sys-pulse { display:flex; align-items:center; gap:5px; font-size:10px; font-weight:600; }
        .sys-pulse .dot { width:7px; height:7px; border-radius:50%; }
        .sys-pulse .dot.online  { background:#4ade80; animation:pulse 2s infinite; }
        .sys-pulse .dot.offline { background:#ef4444; }
        @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.35;} }

        /* -- Activity feed -- */
        .activity-feed { max-height:320px; overflow-y:auto; }
        .activity-feed::-webkit-scrollbar { width:4px; }
        .activity-feed::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.08); border-radius:2px; }
        .activity-item { display:flex; gap:10px; padding:9px 0; border-bottom:1px solid rgba(255,255,255,0.04); font-size:12px; }
        .activity-item:last-child { border-bottom:none; }
        .activity-dot { width:6px; height:6px; border-radius:50%; margin-top:6px; flex-shrink:0; }
        .activity-dot.auth    { background:#818cf8; }
        .activity-dot.order   { background:#fbbf24; }
        .activity-dot.payment { background:#4ade80; }
        .activity-dot.service { background:#38bdf8; }
        .activity-dot.system  { background:#6b7280; }
        .activity-dot.email   { background:#f87171; }
        .activity-dot.product { background:#c084fc; }
        .activity-body { flex:1; min-width:0; }
        .activity-body .activity-desc { color:#cbd5e1; line-height:1.4; word-break:break-word; }
        .activity-body .activity-meta { color:#6b7280; margin-top:2px; font-size:11px; }
        .activity-empty { text-align:center; color:#6b7280; padding:24px; font-size:12px; font-style:italic; }

        .col-right { display:flex; flex-direction:column; gap:20px; }
        .panel-separator { border:none; border-top:1px solid rgba(255,255,255,0.06); margin:14px 0; }
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

            <!-- REVENUE COMPARISON -->
            <div class="rev-compare">
                <div>
                    <div class="rev-month">This Month</div>
                    <div class="rev-amount"><?= number_format($revenueComp['this_month'], 0, ',', '.') ?> ₫</div>
                </div>
                <div class="rev-sep">vs</div>
                <div>
                    <div class="rev-month">Last Month</div>
                    <div class="rev-amount" style="color:#94a3b8;"><?= number_format($revenueComp['last_month'], 0, ',', '.') ?> ₫</div>
                </div>
                <div class="rev-change <?= $revenueComp['pct'] >= 0 ? 'up' : 'down' ?>">
                    <span class="rev-arrow"><?= $revenueComp['pct'] >= 0 ? '▲' : '▼' ?></span>
                    <?= abs($revenueComp['pct']) ?>%
                </div>
                <div style="margin-left:auto;font-size:11px;color:#6b7280;">
                    <?= $revenueComp['change'] >= 0 ? '+' : '' ?><?= number_format($revenueComp['change'], 0, ',', '.') ?> ₫ vs previous
                </div>
            </div>

            <!-- STAT CARDS -->
            <div class="stat-cards">
                <div class="stat-card accent-blue">
                    <div class="stat-icon">₫</div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value"><?= number_format($stats['total_revenue'], 0, ',', '.') ?> ₫</div>
                    <div class="stat-sub">All time</div>
                </div>
                <div class="stat-card accent-green">
                    <div class="stat-icon">#</div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value"><?= $stats['total_orders'] ?></div>
                    <div class="stat-sub">All time</div>
                </div>
                <div class="stat-card accent-amber">
                    <div class="stat-icon">◆</div>
                    <div class="stat-label">Customers</div>
                    <div class="stat-value"><?= $stats['total_users'] ?></div>
                    <div class="stat-sub">Registered users</div>
                </div>
                <div class="stat-card accent-teal">
                    <div class="stat-icon">⬡</div>
                    <div class="stat-label">Active Services</div>
                    <div class="stat-value"><?= $stats['active_services'] ?></div>
                    <div class="stat-sub">Running VPS</div>
                </div>
                <div class="stat-card accent-amber">
                    <div class="stat-icon">◷</div>
                    <div class="stat-label">Pending Orders</div>
                    <div class="stat-value"><?= $stats['pending_orders'] ?></div>
                    <div class="stat-sub">Awaiting payment</div>
                </div>
                <div class="stat-card accent-blue">
                    <div class="stat-icon">◈</div>
                    <div class="stat-label">Today's Revenue</div>
                    <div class="stat-value"><?= number_format($stats['today_revenue'], 0, ',', '.') ?> ₫</div>
                    <div class="stat-sub"><?= date('d/m/Y') ?></div>
                </div>
            </div>

            <!-- MAIN GRID -->
            <div class="dash-grid">
                <!-- LEFT COLUMN -->
                <div>
                    <!-- REVENUE CHART -->
                    <div class="panel chart-wrap">
                        <h3>Revenue Trend</h3>
                        <canvas id="revenueChart" height="90"></canvas>
                    </div>

                    <!-- ALL PRODUCT SALES -->
                    <div class="panel">
                        <h3>Product Sales</h3>
                        <div class="product-bars">
                            <?php $ci = 1; $maxSold = !empty($allProductSales) ? max(array_column($allProductSales, 'total_sold')) ?: 1 : 1;
                            foreach ($allProductSales as $ps):
                                $pct = ($ps['total_sold'] / $maxSold) * 100;
                            ?>
                                <div class="product-bar">
                                    <div class="bar-label">
                                        <span><?= htmlspecialchars($ps['product_name']) ?></span>
                                        <span>
                                            <?php if ($ps['total_sold'] > 0): ?>
                                                <strong><?= $ps['total_sold'] ?></strong> sold · <?= number_format($ps['total_revenue'], 0, ',', '.') ?> ₫
                                            <?php else: ?>
                                                <span style="color:#6b7280;">No sales yet</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="bar-track">
                                        <div class="bar-fill c<?= $ci % 6 + 1 ?>" style="width:<?= $pct ?>%"></div>
                                    </div>
                                </div>
                            <?php $ci++; endforeach; ?>
                            <?php if (empty($allProductSales)): ?>
                                <div class="activity-empty">No products configured.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="col-right">
                    <!-- ORDER STATUS + SERVICE HEALTH -->
                    <div class="panel">
                        <h3>Order Status</h3>
                        <div style="position:relative; height:200px; margin:0 auto; max-width:280px;">
                            <canvas id="orderChart"></canvas>
                        </div>
                        <hr class="panel-separator">
                        <div class="svc-mini">
                            <?php foreach ([['green','Running',$serviceCounts['running']],['yellow','Stopped',$serviceCounts['stopped']],['gray','Suspended',$serviceCounts['suspended']]] as $s): ?>
                                <div class="svc-dot">
                                    <span class="svc-dot-indicator <?= $s[0] ?>"></span>
                                    <?= $s[1] ?> <span><?= $s[2] ?></span>
                                </div>
                            <?php endforeach; ?>
                            <div style="flex:1;"></div>
                            <div style="display:flex;gap:12px;">
                                <div class="sys-pulse">
                                    <span class="dot <?= $systemHealth['database'] ? 'online' : 'offline' ?>"></span>
                                    <span style="color:<?= $systemHealth['database'] ? '#4ade80' : '#ef4444' ?>">DB</span>
                                </div>
                                <div class="sys-pulse">
                                    <span class="dot <?= $systemHealth['vm_bridge'] ? 'online' : 'offline' ?>"></span>
                                    <span style="color:<?= $systemHealth['vm_bridge'] ? '#4ade80' : '#ef4444' ?>">Bridge</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TOP CUSTOMERS -->
                    <div class="panel">
                        <h3>Top Customers</h3>
                        <?php if (empty($topCustomers)): ?>
                            <div class="activity-empty">No customers yet.</div>
                        <?php else: ?>
                            <table class="cust-table">
                                <thead>
                                    <tr><th>Customer</th><th>Tier</th><th style="text-align:right;">Spent</th><th style="text-align:right;">Orders</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topCustomers as $c): ?>
                                        <tr>
                                            <td><span class="cust-name"><?= htmlspecialchars($c['name']) ?></span></td>
                                            <td><span class="tier-badge <?= $c['tier'] ?>"><?= ucfirst($c['tier']) ?></span></td>
                                            <td style="text-align:right;"><?= number_format($c['total_spent'], 0, ',', '.') ?> ₫</td>
                                            <td style="text-align:right;"><?= $c['order_count'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <!-- RECENT ACTIVITY -->
                    <div class="panel">
                        <h3>Recent Activity</h3>
                        <div class="activity-feed">
                            <?php if (empty($recentActivity)): ?>
                                <div class="activity-empty">No activity recorded yet.</div>
                            <?php else: ?>
                                <?php foreach ($recentActivity as $a):
                                    $actionParts = explode('.', $a['action']);
                                    $category = $actionParts[0] ?? 'system';
                                    $ts = strtotime($a['created_at']);
                                ?>
                                    <div class="activity-item">
                                        <div class="activity-dot <?= htmlspecialchars($category) ?>"></div>
                                        <div class="activity-body">
                                            <div class="activity-desc"><?= htmlspecialchars($a['description']) ?></div>
                                            <div class="activity-meta">
                                                <?= $a['user_name'] ? htmlspecialchars($a['user_name']) . ' · ' : '' ?>
                                                <?= date('d/m H:i', $ts) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function(){
        var ctx = document.getElementById('revenueChart').getContext('2d');
        var labels = <?= (isset($chartLabelsJson) && $chartLabelsJson) ? $chartLabelsJson : json_encode([]) ?>;
        var dataPoints = <?= (isset($chartDataJson) && $chartDataJson) ? $chartDataJson : json_encode([]) ?>;
        var gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(56,189,248,0.35)');
        gradient.addColorStop(1, 'rgba(56,189,248,0)');
        new Chart(ctx, {
            type: 'line', data: { labels: labels, datasets: [{ label: 'Revenue', data: dataPoints, borderColor: '#38bdf8', backgroundColor: gradient, borderWidth: 2, pointBackgroundColor: '#fff', pointBorderColor: '#38bdf8', pointRadius: 3, fill: true, tension: 0.4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#6b7280', font: { size: 10 } } }, x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#6b7280', font: { size: 10 } } } } }
        });

        var octx = document.getElementById('orderChart').getContext('2d');
        var oLabels = <?= (isset($orderLabelsJson) && $orderLabelsJson) ? $orderLabelsJson : json_encode([]) ?>;
        var oData = <?= (isset($orderDataJson) && $orderDataJson) ? $orderDataJson : json_encode([]) ?>;
        var colors = { pending:'#fbbf24', confirmed:'#38bdf8', provisioning:'#818cf8', active:'#c084fc', success:'#4ade80', cancelled:'#6b7280' };
        new Chart(octx, {
            type: 'doughnut', data: { labels: oLabels, datasets: [{ data: oData, backgroundColor: oLabels.map(function(l){ return colors[l]||'#374151'; }), borderColor: '#141414', borderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8', padding: 14, font: { size: 11 }, usePointStyle: true, pointStyleWidth: 8 } } } }
        });
    })();
    </script>
</body>
</html>