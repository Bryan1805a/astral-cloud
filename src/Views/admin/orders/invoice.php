<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 30px 40px; }
    body {
        font-family: "DejaVu Sans", "Helvetica", sans-serif;
        font-size: 12px;
        color: #1e293b;
        line-height: 1.5;
    }
    .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #38bdf8; }
    .header h1 { color: #0f172a; font-size: 24px; margin: 0 0 4px 0; }
    .header .sub { color: #64748b; font-size: 13px; }
    .header .badge {
        display: inline-block; margin-top: 8px; padding: 4px 14px; border-radius: 12px;
        font-size: 11px; font-weight: bold; text-transform: uppercase;
    }
    .badge-success { background: #dcfce7; color: #166534; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-danger  { background: #fee2e2; color: #991b1b; }
    .badge-info    { background: #e0f2fe; color: #075985; }
    .badge-secondary { background: #f1f5f9; color: #475569; }
    .row { width: 100%; }
    .col-left  { float: left; width: 50%; }
    .col-right { float: right; width: 50%; text-align: right; }
    .info-box  { margin-bottom: 24px; }
    .info-box h3 { font-size: 13px; color: #38bdf8; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 1px; }
    .info-box p { margin: 2px 0; color: #334155; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    th { background: #f8fafc; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; }
    td { font-size: 12px; }
    .amount { text-align: right; }
    .totals { margin-top: 16px; }
    .totals table { margin: 0; }
    .totals td { border: none; padding: 3px 10px; }
    .totals .label { text-align: right; color: #64748b; width: 80%; }
    .totals .value { text-align: right; width: 20%; }
    .grand-total td { font-size: 14px; font-weight: bold; border-top: 2px solid #38bdf8 !important; padding-top: 8px; }
    .grand-total .label { color: #0f172a; }
    .grand-total .value { color: #38bdf8; }
    .footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center; }
    .clearfix::after { content: ""; clear: both; display: table; }
</style>
</head>
<body>

<div class="header">
    <h1>ASTRAL CLOUD</h1>
    <div class="sub">Virtual Server Solutions — Electronic Invoice</div>
    <div>
        <?php
            $bgClass = "badge-secondary";
            if ($order["status"] === "success" || $order["status"] === "active") $bgClass = "badge-success";
            elseif ($order["status"] === "pending") $bgClass = "badge-warning";
            elseif ($order["status"] === "cancelled") $bgClass = "badge-danger";
            elseif ($order["status"] === "confirmed" || $order["status"] === "provisioning") $bgClass = "badge-info";
        ?>
        <span class="badge <?= $bgClass ?>"><?= strtoupper($order["status"]) ?></span>
    </div>
</div>

<div class="row clearfix">
    <div class="col-left">
        <div class="info-box">
            <h3>Customer</h3>
            <p><strong><?= htmlspecialchars($order["customer_name"]) ?></strong></p>
            <p><?= htmlspecialchars($order["customer_email"]) ?></p>
            <p><?= htmlspecialchars($order["customer_phone"] ?? "") ?></p>
            <p>Tier: <?= strtoupper($order["customer_tier"]) ?></p>
        </div>
    </div>
    <div class="col-right">
        <div class="info-box">
            <h3>Invoice</h3>
            <p><strong>Invoice #:</strong> INV-<?= str_pad($order["order_id"], 6, "0", STR_PAD_LEFT) ?></p>
            <p><strong>Order #:</strong> <?= $order["order_id"] ?></p>
            <p><strong>Date:</strong> <?= date("d/m/Y H:i", strtotime($order["created_at"])) ?></p>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:40%;">VPS Package</th>
            <th style="width:25%;">Specifications</th>
            <th style="width:10%;">Qty</th>
            <th style="width:12%;" class="amount">Unit Price</th>
            <th style="width:13%;" class="amount">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($order["items"] as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item["product_name"]) ?></td>
            <td><?= htmlspecialchars($item["product_cpu"]) ?> / <?= htmlspecialchars($item["product_ram"]) ?> / <?= htmlspecialchars($item["product_storage"]) ?></td>
            <td><?= $item["quantity"] ?></td>
            <td class="amount"><?= number_format($item["unit_price"], 0, ",", ".") ?> VND</td>
            <td class="amount"><?= number_format($item["subtotal"], 0, ",", ".") ?> VND</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="totals">
    <table>
        <tr>
            <td class="label">Subtotal</td>
            <td class="value"><?= number_format($order["subtotal"], 0, ",", ".") ?> VND</td>
        </tr>
        <?php if ($order["discount_amount"] > 0): ?>
        <tr>
            <td class="label">Discount<?= $order["voucher_code"] ? " (" . htmlspecialchars($order["voucher_code"]) . ")" : "" ?></td>
            <td class="value" style="color:#16a34a;">- <?= number_format($order["discount_amount"], 0, ",", ".") ?> VND</td>
        </tr>
        <?php endif; ?>
        <tr>
            <td class="label">VAT (0%)</td>
            <td class="value">0 VND</td>
        </tr>
        <tr class="grand-total">
            <td class="label">Total</td>
            <td class="value"><?= number_format($order["total_price"], 0, ",", ".") ?> VND</td>
        </tr>
    </table>
</div>

<?php if ($order["note"]): ?>
<div class="info-box" style="margin-top:20px;">
    <h3>Notes</h3>
    <p style="color:#64748b;"><?= nl2br(htmlspecialchars($order["note"])) ?></p>
</div>
<?php endif; ?>

<div class="footer">
    Astral Cloud — Virtual Server Solutions<br>
    This invoice was generated automatically. Thank you for your business!
</div>

</body>
</html>
