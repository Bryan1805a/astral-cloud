<?php
class AdminEmail {
    // Admin send message
    public static function send(int $senderId, ?int $recipientId, string $subject, string $body): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO admin_emails (sender_id, recipient_id, subject, body) VALUES (?, ?, ?, ?)");
        $stmt->execute([$senderId, $recipientId, $subject, $body]);
    }

    // User read email
    public static function getInboxForUser(int $userId): array {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT e.*, u.name AS sender_name 
            FROM admin_emails e
            JOIN users u ON e.sender_id = u.id
            WHERE e.recipient_id = ? OR e.recipient_id IS NULL
            ORDER BY e.sent_at DESC
        ");

        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // User seen status
    public static function markAsRead(int $emailId, int $userId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE admin_emails SET is_read = 1 WHERE id = ? AND (recipient_id = ? OR recipient_id IS NULL)");
        $stmt->execute([$emailId, $userId]);
    }
}