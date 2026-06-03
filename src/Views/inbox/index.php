<h2 class="mb-4 fw-bold text-info"><i class="bi bi-envelope"></i> Inbox</h2>

<?php if (empty($emails)): ?>
    <div class="glass-panel p-5 text-center">
        <i class="bi bi-inbox text-secondary" style="font-size: 4rem;"></i>
        <h4 class="mt-3 text-secondary">No messages yet.</h4>
        <p>You will receive notifications and announcements here.</p>
        <a href="/" class="btn btn-primary mt-2">Back to Home</a>
    </div>
<?php else: ?>
    <div class="glass-panel p-4">
        <div class="table-responsive">
            <table class="table table-glass mb-0">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emails as $msg): ?>
                        <tr>
                            <td><?= htmlspecialchars($msg['sender_name']) ?></td>
                            <td><?= htmlspecialchars($msg['subject']) ?></td>
                            <td class="text-secondary"><?= date('d/m/Y H:i', strtotime($msg['sent_at'])) ?></td>
                            <td>
                                <?php if ($msg['is_read']): ?>
                                    <span class="badge bg-secondary">Read</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">New</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($msg['body'])): ?>
                            <tr>
                                <td colspan="4" class="bg-dark bg-opacity-25">
                                    <div class="p-3 text-secondary"><?= nl2br(htmlspecialchars($msg['body'])) ?></div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
