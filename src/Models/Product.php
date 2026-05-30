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

    // Add the new VPS Pack (New product)
    public static function create(array $data): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO products (name, slug, description, cpu, ram, storage, bandwidth, price, stock, is_active, created_by)
            VALUES (:name, :slug, :description, :cpu, :ram, :storage, :bandwidth, :price, :stock, :is_active, :created_by)
        ");

        $stmt->execute($data);
    }

    // Update VPS package (Update product information)
    public static function update(int $id, array $data): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE products 
            SET name = :name, description = :description, cpu = :cpu, ram = :ram, 
                storage = :storage, bandwidth = :bandwidth, price = :price, 
                stock = :stock, is_active = :is_active
            WHERE id = :id
        ");

        $data["id"] = $id;
        $stmt->execute($data);
    }
}
