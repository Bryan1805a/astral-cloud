<?php

class Order {
    // Create order and save to database
    public static function create(int $userId, ?int $voucherId, ?string $voucherCode, float $subtotal, float $discount, float $total, string $note): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, voucher_id, voucher_code, subtotal, discount_amount, total_price, note, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$userId, $voucherId, $voucherCode, $subtotal, $discount, $total, $note]);
        return (int) $pdo->lastInsertId();
    }

    // Add items to existing order
    public static function addItem(int $orderId, int $productId, string $name, string $cpu, string $ram, string $storage, float $price, int $quantity, float $subtotal): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_cpu, product_ram, product_storage, unit_price, quantity, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $productId, $name, $cpu, $ram, $storage, $price, $quantity, $subtotal]);
    }

    // Save voucher usage history
    public static function recordVoucherUsage(int $voucherId, int $userId, int $orderId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO voucher_usages (voucher_id, user_id, order_id) VALUES (?, ?, ?)");
        $stmt->execute([$voucherId, $userId, $orderId]);
    }

    // Find order by ID
    public static function findById(int $orderId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetch() ?: null;
    }

    // Get user's order information
    public static function getUserOrders(int $userId): array {
        $pdo = Database::getConnection();
        $sql = "
            SELECT 
                o.id AS order_id, 
                o.total_price, 
                o.status AS order_status, 
                o.created_at,
                oi.product_name, 
                oi.product_cpu, 
                oi.product_ram
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = :user_id
            ORDER BY o.created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["user_id" => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
