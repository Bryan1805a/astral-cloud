<section class="page-section">
    <h2 style="margin-bottom:24px;">Inbox</h2>

    <?php if (empty($messages)): ?>
        <div style="padding:60px 40px;text-align:center;border-radius:24px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
            <div style="font-size:48px;margin-bottom:16px;">📬</div>
            <h3 style="color:#6b7280;">No messages yet</h3>
            <p style="color:#6b7280;">You will receive notifications and announcements here.</p>
            <a href="/" class="checkout-btn" style="display:inline-block;margin-top:12px;">Back to Home</a>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
            <div style="padding:20px 24px;border-radius:20px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                            <strong style="font-size:17px;"><?= htmlspecialchars($msg['title']) ?></strong>
                            <?php if (!$msg['is_read']): ?>
                                <span style="padding:3px 10px;border-radius:999px;font-size:11px;font-weight:800;background:#38bdf8;color:#000;">NEW</span>
                            <?php else: ?>
                                <span style="padding:3px 10px;border-radius:999px;font-size:11px;font-weight:800;background:rgba(255,255,255,0.1);color:#6b7280;">READ</span>
                            <?php endif; ?>
                            <span style="padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;background:rgba(255,255,255,0.06);color:#6b7280;">
                                <?= $msg['type'] === 'email' ? 'Admin' : 'System' ?>
                            </span>
                        </div>
                        <div style="color:#6b7280;font-size:13px;margin-top:4px;">
                            From <strong style="color:#b8b8b8;"><?= htmlspecialchars($msg['from']) ?></strong>
                            &middot; <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <?php if ($msg['link']): ?>
                            <a href="<?= htmlspecialchars($msg['link']) ?>" style="padding:8px 16px;border-radius:10px;border:1px solid rgba(56,189,248,0.3);background:transparent;color:#38bdf8;text-decoration:none;font-size:13px;font-weight:600;">View</a>
                        <?php endif; ?>
                        <?php if (!$msg['is_read']): ?>
                            <form action="/inbox/read" method="POST" style="margin:0;">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                <input type="hidden" name="type" value="<?= $msg['type'] ?>">
                                <button type="submit" style="padding:8px 16px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:transparent;color:#b8b8b8;cursor:pointer;font-size:13px;">Mark read</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($msg['body'])): ?>
                    <div style="margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,0.06);color:#b8b8b8;font-size:14px;line-height:1.6;">
                        <?= nl2br(htmlspecialchars($msg['body'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
