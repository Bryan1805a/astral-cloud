<?php

class Product {
    // Get all active products in database
    public static function getActive(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 ORDER BY price ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Check if a product is active
    public static function findActive(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id AND is_active = 1 LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // Get all products information - including hidden (For admin)
    public static function getAllForAdmin(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
