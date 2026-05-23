<?php

class Product {
    public static function getActive(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 ORDER BY price ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function findActive(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id AND is_active = 1 LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
