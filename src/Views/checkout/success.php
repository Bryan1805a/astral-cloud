<section class="checkout-section">
    <div style="text-align:center;padding:80px 24px;max-width:600px;margin:0 auto;">
        <h1>Order Confirmed!</h1>
        <p style="color:#b8b8b8;font-size:18px;">Your VPS order has been placed and confirmed successfully.</p>
        <div style="margin:32px 0;padding:24px;border-radius:20px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
            <p style="color:#b8b8b8;margin-bottom:8px;">Order Code</p>
            <p style="font-size:24px;font-weight:800;letter-spacing:2px;color:#fff;">#<?= htmlspecialchars($order['id']) ?></p>
        </div>
        <p style="color:#6b7280;font-size:14px;">Thank you for choosing Astral Cloud. Your server will be activated within 5–10 minutes.</p>
        <a href="/orders" class="checkout-btn" style="display:inline-block;margin-top:24px;">View My Orders</a>
    </div>
</section>
