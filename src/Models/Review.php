<?php
namespace App\Models;

use App\Core\Database;

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

        return $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Change review status
    // Hide / Show
    public static function toggleVisibility(int $id): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE reviews SET is_visible = NOT is_visible WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Submit a new review
    public static function create(int $userId, int $productId, ?int $orderId, int $rating, string $comment): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, product_id, order_id, rating, comment, is_visible)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$userId, $productId, $orderId, $rating, $comment]);
    }

    // Get visible reviews for a product
    public static function getByProduct(int $productId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT r.*, u.name AS user_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.is_visible = 1
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Check if a user can review a product (has a success order and hasn't already reviewed it)
    public static function canReview(int $userId, int $productId): ?array {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT o.id AS order_id, oi.id AS order_item_id
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
              AND oi.product_id = ?
              AND o.status = 'success'
              AND o.id NOT IN (
                  SELECT order_id FROM reviews WHERE user_id = ? AND product_id = ?
              )
            ORDER BY o.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId, $productId, $userId, $productId]);
        return $stmt->fetch() ?: null;
    }
}