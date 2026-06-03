<?php
class Review {
    // Get all reviews + user information + product information
    public static function getAllForAdmin(): array {
        $pdo = Database::getConnection();
        $sql = "
            SELECT r.*, u.name AS customer_name, p.name AS product_name 
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN products p ON r.product_id = p.id
            ORDER BY r.created_at DESC
        ";

        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Change review status
    // Hide / Show
    public static function toggleVisibility(int $id): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE reviews SET is_visible = NOT is_visible WHERE id = ?");
        $stmt->execute([$id]);
    }
}