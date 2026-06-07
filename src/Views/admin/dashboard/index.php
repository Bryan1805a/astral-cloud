<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Revenue Overview | Astral Cloud Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/base.css">
    <link rel="stylesheet" href="/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 border-bottom border-secondary mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-warning" href="/admin"><i class="bi bi-shield-lock-fill"></i> ASTRAL ADMIN</a>
            <div class="navbar-nav">
                    <a class="nav-link active text-info" href="/admin">Overview</a>
                    <a class="nav-link" href="/admin/orders">Orders</a>
                    <a class="nav-link" href="/admin/products">Products</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <h3 class="fw-bold text-info mb-4">System Overview</h3>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="glass-panel p-4 text-center border-bottom border-info border-3">
                    <h5 class="text-secondary">Total Revenue</h5>
                    <h2 class="fw-bold text-info"><?= number_format($stats['total_revenue'], 0, ',', '.') ?> VND</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-panel p-4 text-center border-bottom border-success border-3">
                    <h5 class="text-secondary">Total Orders</h5>
                    <h2 class="fw-bold text-success"><?= $stats['total_orders'] ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-panel p-4 text-center border-bottom border-warning border-3">
                    <h5 class="text-secondary">Customers</h5>
                    <h2 class="fw-bold text-warning"><?= $stats['total_users'] ?></h2>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-panel p-4 h-100">
                    <h5 class="text-cyan mb-4">Revenue Chart (Recent Months)</h5>
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-panel p-4 mb-4">
                    <h5 class="text-success mb-3"><i class="bi bi-graph-up-arrow"></i> Top Selling</h5>
                    <ul class="list-group list-group-flush bg-transparent">
                        <?php foreach ($topProducts as $tp): ?>
                            <li class="list-group-item bg-transparent text-light border-secondary d-flex justify-content-between px-0">
                                <span><?= htmlspecialchars($tp['product_name']) ?></span>
                                <span class="badge bg-success rounded-pill"><?= $tp['total_sold'] ?> sold</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="glass-panel p-4">
                    <h5 class="text-danger mb-3"><i class="bi bi-graph-down-arrow"></i> Slowest Selling</h5>
                    <ul class="list-group list-group-flush bg-transparent">
                        <?php foreach ($worstProducts as $wp): ?>
                            <li class="list-group-item bg-transparent text-light border-secondary d-flex justify-content-between px-0">
                                <span><?= htmlspecialchars($wp['product_name']) ?></span>
                                <span class="badge bg-secondary rounded-pill"><?= $wp['total_sold'] ?> sold</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        // Get JSON data from PHP (fall back to empty arrays if missing)
        const labels = <?= (isset($chartLabelsJson) && $chartLabelsJson) ? $chartLabelsJson : json_encode([]) ?>;
        const dataPoints = <?= (isset($chartDataJson) && $chartDataJson) ? $chartDataJson : json_encode([]) ?>;

        // Debug: log data to the console to help diagnose VPS rendering issues
        console.log('Chart labels:', labels);
        console.log('Chart data:', dataPoints);

        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(56, 189, 248, 0.5)'); // Light cyan at top
        gradient.addColorStop(1, 'rgba(56, 189, 248, 0)');   // Transparent at bottom

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (VND)',
                    data: dataPoints,
                    borderColor: '#38bdf8', // Cyan line color
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#38bdf8',
                    fill: true,
                    tension: 0.4 // Smooth the line
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: { color: '#94a3b8' }
                    },
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: { color: '#94a3b8' }
                    }
                }
            }
        });
    </script>
</body>
</html>