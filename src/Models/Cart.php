<?php

class Cart {
    public static function getUserCart(int $userId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.product_id, c.quantity, p.name, p.price, p.cpu, p.ram, p.storage
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function add(int $userId, int $productId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO cart (user_id, product_id, quantity)
            VALUES (:user_id, :product_id, 1)
            ON DUPLICATE KEY UPDATE quantity = quantity + 1
        ");
        $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
    }

    public static function remove(int $userId, int $productId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
    }

    public static function clear(int $userId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
    }

    public static function updateQuantity(int $userId, int $productId, int $quantity): void {
        $pdo = Database::getConnection();
        if ($quantity <= 0) {
            self::remove($userId, $productId);
        } else {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->execute(['quantity' => $quantity, 'user_id' => $userId, 'product_id' => $productId]);
        }
    }

    public static function getCartCount(int $userId): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    }
}
