<?php
    session_start();
    require_once "config/db.php";
    
    // Query to retrieve active VPS packages (is_active = 1)
    // Sort by price, asc order

    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 ORDER BY price ASC");
        $stmt->execute();

        // return as an array
        $vps_plans = $stmt->fetchAll();
    } catch (PDOException $e) {
        die("Data retrieval error: " . $e->getMessage());
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Astral Cloud - Virtual Server Solutions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        /* CSS nháp cho Thành viên A: Hiệu ứng Glassmorphism */
        body {
            background-color: #0f172a; /* Màu nền Dark Blue hiện đại */
            color: #f8fafc;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7); /* Nền mờ có độ trong suốt */
            backdrop-filter: blur(10px); /* Hiệu ứng Blur */
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
            border-color: rgba(56, 189, 248, 0.5); /* Sáng viền màu xanh cyan khi hover */
        }
        .price-text {
            color: #38bdf8; /* Cyan Tech */
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 border-bottom border-secondary mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold text-info" href="index.php">
                <i class="bi bi-cloud-lightning-fill"></i> ASTRAL CLOUD
            </a>
            <div class="d-flex">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span class="navbar-text me-3">
                        Hello, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong> 
                        (Rank: <?= htmlspecialchars($_SESSION['user_tier'] ?? 'Silver') ?>)
                    </span>
                    <a href="cart.php" class="btn btn-outline-info btn-sm me-2">Cart</a>
                    <?php if($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff'): ?>
                        <a href="admin/index.php" class="btn btn-outline-warning btn-sm me-2">Admin Panel</a>
                    <?php endif; ?>
                    <a href="auth/logout.php" class="btn btn-outline-danger btn-sm">Log out</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn btn-outline-info btn-sm me-2">Log in</a>
                    <a href="auth/register.php" class="btn btn-primary btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold">Deploy the system quickly.</h1>
            <p class="lead text-secondary">Create a powerful Cloud VPS in just 60 seconds.</p>
        </div>

        <div class="row g-4">
            <?php foreach ($vps_plans as $plan): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card glass-card h-100 text-light p-3">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-info">
                                <i class="bi bi-server"></i> <?= htmlspecialchars($plan['name']) ?>
                            </h5>
                            <p class="card-text text-secondary small"><?= htmlspecialchars($plan['description']) ?></p>
                            
                            <ul class="list-unstyled mt-3 mb-4">
                                <li class="mb-2"><i class="bi bi-cpu text-primary me-2"></i> <strong>CPU:</strong> <?= htmlspecialchars($plan['cpu']) ?></li>
                                <li class="mb-2"><i class="bi bi-memory text-primary me-2"></i> <strong>RAM:</strong> <?= htmlspecialchars($plan['ram']) ?></li>
                                <li class="mb-2"><i class="bi bi-hdd-network text-primary me-2"></i> <strong>Lưu trữ:</strong> <?= htmlspecialchars($plan['storage']) ?></li>
                                <li class="mb-2"><i class="bi bi-speedometer2 text-primary me-2"></i> <strong>Mạng:</strong> <?= htmlspecialchars($plan['bandwidth']) ?></li>
                            </ul>
                            
                            <div class="mt-auto border-top border-secondary pt-3 text-center">
                                <div class="price-text mb-3">
                                    <?= number_format($plan['price'], 0, ',', '.') ?> VND <span class="fs-6 text-secondary fw-normal">/month</span>
                                </div>
                                
                                <form action="cart_process.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $plan['id'] ?>">
                                    <button type="submit" class="btn btn-info w-100 fw-bold">
                                        <i class="bi bi-cart-plus"></i> Register configuration
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
    </div>
</body>
</html>