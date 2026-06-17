<?php
namespace App\Models;

use App\Core\Database;

class Product {
    // Get all active products in database
    public static function getActive(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_active = 1 ORDER BY price ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get filtered and sorted active products
    public static function getFiltered(string $search = '', string $sort = 'price_asc'): array {
        $pdo = Database::getConnection();

        $where  = ['is_active = 1'];
        $params = [];

        if ($search !== '') {
            $where[] = "(name LIKE ? OR description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sortMap = [
            'price_asc'  => 'price ASC',
            'price_desc' => 'price DESC',
            'rating'     => 'id ASC', // fallback — rating needs join; sorted in PHP
            'name'       => 'name ASC',
        ];

        $orderBy = $sortMap[$sort] ?? 'price ASC';

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT * FROM products WHERE {$whereClause} ORDER BY {$orderBy}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        if ($sort === 'rating') {
            $ratings = [];
            $all = $pdo->query("
                SELECT p.id, COALESCE(AVG(r.rating), 0) AS avg_rating, COUNT(r.id) AS review_count
                FROM products p
                LEFT JOIN reviews r ON p.id = r.product_id AND r.is_visible = 1
                WHERE p.is_active = 1
                GROUP BY p.id
            ")->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($all as $row) {
                $ratings[(int)$row['id']] = (float)$row['avg_rating'];
            }

            usort($results, function ($a, $b) use ($ratings) {
                $ra = $ratings[(int)$a['id']] ?? 0;
                $rb = $ratings[(int)$b['id']] ?? 0;
                return $rb <=> $ra;
            });
        }

        return $results;
    }

    // Check if a product is active
    public static function findActive(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id AND is_active = 1 LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // Find a single active product by slug
    public static function findBySlug(string $slug): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE slug = :slug AND is_active = 1 LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    // Get all products information - including hidden (For admin)
    public static function getAllForAdmin(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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

    // Soft delete
    // Only toggle active status to keep Order History
    public static function toggleActive(int $id): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
    }
}
