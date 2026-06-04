<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Management | Astral Cloud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background-color: #0f172a; 
            color: #f8fafc; 
        }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
        }
        .status-badge {
            width: 100px;
            display: inline-block;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-info"><i class="bi bi-server"></i> Your Server Services</h2>
            <a href="/" class="btn btn-outline-light"><i class="bi bi-house-door"></i> Back to Home</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
            <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show mb-4">
                Order cancelled successfully! Voucher code (if any) has been refunded.
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['err']) && $_GET['err'] === 'cannot_cancel'): ?>
            <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show mb-4">
                This order cannot be canceled (it has been processed or does not exist).
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="glass-panel p-5 text-center">
                <i class="bi bi-hdd-network text-secondary" style="font-size: 4rem;"></i>
                <h4 class="text-secondary mt-3">You have no active services yet</h4>
                <a href="/" class="btn btn-primary mt-3">Create a VPS now</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="glass-panel p-4 h-100 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-3 mb-3">
                                <div>
                                    <h5 class="mb-0 text-cyan">Order #<?= $order['order_id'] ?></h5>
                                    <small class="text-secondary"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
                                </div>
                                
                                <?php
                                    $bgClass = 'bg-secondary';
                                    if ($order['order_status'] === 'pending') $bgClass = 'bg-warning text-dark';
                                    if ($order['order_status'] === 'provisioning') $bgClass = 'bg-info text-dark';
                                    if ($order['order_status'] === 'running' || $order['order_status'] === 'success') $bgClass = 'bg-success';
                                    if ($order['order_status'] === 'cancelled') $bgClass = 'bg-danger';
                                ?>
                                <span class="badge rounded-pill <?= $bgClass ?> status-badge">
                                    <?= strtoupper($order['order_status']) ?>
                                </span>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold text-light"><i class="bi bi-hdd-rack"></i> <?= htmlspecialchars($order['product_name']) ?></h6>
                                <p class="mb-1 text-secondary small"><i class="bi bi-cpu"></i> CPU: <?= htmlspecialchars($order['product_cpu']) ?></p>
                                <p class="mb-1 text-secondary small"><i class="bi bi-memory"></i> RAM: <?= htmlspecialchars($order['product_ram']) ?></p>
                                <p class="mb-0 text-info fw-bold mt-2"><?= number_format($order['total_price'], 0, ',', '.') ?> VND / period</p>
                            </div>

                            <div class="mt-auto d-flex gap-2">
                                <button class="btn btn-sm btn-outline-info flex-grow-1" onclick="alert('Tính năng xem chi tiết tiến trình đang phát triển!')">
                                    <i class="bi bi-info-circle"></i> Detail
                                </button>
                                
                                <?php if ($order['order_status'] === 'success' || $order['order_status'] === 'running'): ?>
                                    <button class="btn btn-sm btn-success flex-grow-1" onclick="openConsole('<?= htmlspecialchars($order['product_name']) ?>')">
                                        <i class="bi bi-terminal"></i> Console
                                    </button>
                                <?php elseif ($order['order_status'] === 'pending'): ?>
                                    <form action="/orders/cancel" method="POST" class="flex-grow-1 d-flex" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary flex-grow-1" disabled>
                                        <i class="bi bi-terminal"></i> Waiting...
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function openConsole(serverName) {
            let hostname = serverName.toLowerCase().replace(/\s+/g, '-');
            alert("Initializing a secure SSH connection to: " + serverName + "\n\nroot@" + hostname + ":~# _");
        }
    </script>
</body>
</html>