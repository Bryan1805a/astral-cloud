<section class="checkout-section">
    <div style="text-align:center;padding:80px 24px;max-width:560px;margin:0 auto;">
        <div style="font-size:64px;margin-bottom:16px;">✅</div>
        <h1 style="font-size:32px;font-weight:900;margin-bottom:12px;"><?= __('success_confirmed') ?></h1>
        <p style="color:#94a3b8;font-size:16px;line-height:1.6;"><?= __('success_p1') ?></p>

        <div style="margin:32px 0;padding:24px;border-radius:20px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);">
            <p style="color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Order Code</p>
            <p style="font-size:28px;font-weight:900;color:#38bdf8;margin:0;">#<?= htmlspecialchars($order['id']) ?></p>
            <p style="color:#6b7280;font-size:13px;margin-top:8px;">Total: <?= number_format($order['total_price'], 0, ',', '.') ?> VND</p>
        </div>

        <div style="margin-bottom:32px;">
            <h2 style="font-size:16px;font-weight:700;color:#e2e8f0;margin-bottom:16px;"><?= __('success_what_next') ?></h2>
            <div style="display:flex;flex-direction:column;gap:12px;text-align:left;">
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:rgba(74,222,128,0.06);border-radius:12px;border:1px solid rgba(74,222,128,0.15);">
                    <span style="font-size:20px;">✓</span>
                    <div>
                        <strong style="font-size:13px;color:#4ade80;"><?= __('success_step1') ?></strong>
                        <p style="font-size:12px;color:#6b7280;margin:2px 0 0;"><?= __('success_step1d') ?></p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:rgba(56,189,248,0.06);border-radius:12px;border:1px solid rgba(56,189,248,0.15);">
                    <span style="font-size:20px;">⏳</span>
                    <div>
                        <strong style="font-size:13px;color:#38bdf8;"><?= __('success_step2') ?></strong>
                        <p style="font-size:12px;color:#6b7280;margin:2px 0 0;"><?= __('success_step2d') ?></p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:rgba(255,255,255,0.02);border-radius:12px;border:1px solid rgba(255,255,255,0.06);">
                    <span style="font-size:20px;">🖥</span>
                    <div>
                        <strong style="font-size:13px;color:#e2e8f0;"><?= __('success_step3') ?></strong>
                        <p style="font-size:12px;color:#6b7280;margin:2px 0 0;"><?= __('success_step3d') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <a href="/orders" class="checkout-btn" style="display:inline-flex;align-items:center;gap:8px;padding:14px 36px;font-size:15px;">
            <?= __('success_goto') ?>
        </a>
        <p style="margin-top:16px;">
            <a href="/docs" style="color:#38bdf8;font-size:13px;text-decoration:none;font-weight:600;"><?= __('success_help') ?></a>
        </p>
    </div>
</section>
