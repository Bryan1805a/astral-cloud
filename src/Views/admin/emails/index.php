<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email | Astral Cloud Admin</title>
    <link rel="stylesheet" href="/css/base.css">
    <style>
        .form-card { background:#151515; border-radius:24px; border:1px solid rgba(255,255,255,0.12); padding:40px; max-width:700px; }
        .form-group { margin-bottom:20px; }
        .form-group label { display:block; margin-bottom:8px; color:#b8b8b8; font-size:14px; font-weight:600; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <div class="admin-sidebar">
            <h2>ASTRAL ADMIN</h2>
            <a href="/admin">Overview</a>
            <a href="/admin/orders">Orders</a>
            <a href="/admin/products">Products</a>
            <a href="/admin/users">Users</a>
            <a href="/admin/vouchers">Vouchers</a>
            <a href="/admin/reviews">Reviews</a>
            <a href="/admin/emails" class="active">Emails</a>
            <a href="/admin/audit-logs">Audit Logs</a>
            <div style="margin-top:auto;padding-top:32px;">
                <a href="/" style="color:#6b7280;">← Back to site</a>
            </div>
        </div>
        <div class="admin-content">
            <h1>Emails</h1>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'sent'): ?>
                <div class="alert alert-success">✓ Email sent successfully!</div>
            <?php endif; ?>

            <div class="form-card">
                <form action="/admin/emails/send" method="POST">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label>Recipient</label>
                        <select name="recipient_id" class="form-select">
                            <option value="">All customers (broadcast)</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= $customer['id'] ?>">
                                    <?= htmlspecialchars($customer['name']) ?> (<?= htmlspecialchars($customer['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="body" class="form-control" rows="6" required></textarea>
                    </div>

                    <button type="submit" class="admin-btn">Send Email</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
