<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Email | Astral Cloud Admin</title>
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-opacity-50 border-bottom border-secondary mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold text-warning" href="/admin"><i class="bi bi-shield-lock-fill"></i> ASTRAL ADMIN</a>
            <div class="navbar-nav">
                <a class="nav-link" href="/admin/orders">Orders</a>
                <a class="nav-link" href="/admin/products">Products</a>
                <a class="nav-link" href="/admin/users">Customers</a>
                <a class="nav-link" href="/admin/vouchers">Promotions</a>
                <a class="nav-link" href="/admin/reviews">Reviews</a>
                <a class="nav-link active text-info" href="/admin/emails">Send Email</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <h3 class="fw-bold text-info mb-4">Send Email to Customers</h3>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'sent'): ?>
            <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show">
                Email sent successfully!
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="glass-panel p-4">
            <form action="/admin/emails/send" method="POST">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label">Recipient</label>
                    <select name="recipient_id" class="form-select bg-dark text-light border-secondary">
                        <option value="">All customers (broadcast)</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>">
                                <?= htmlspecialchars($customer['name']) ?> (<?= htmlspecialchars($customer['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="body" class="form-control bg-dark text-light border-secondary" rows="6" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Send Email</button>
            </form>
        </div>
    </div>
</body>
</html>
