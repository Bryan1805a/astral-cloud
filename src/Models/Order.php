<?php

class Order {
    public static function create(int $userId, ?int $voucherId, ?string $voucherCode, float $subtotal, float $discount, float $total, string $note): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, voucher_id, voucher_code, subtotal, discount_amount, total_price, note, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$userId, $voucherId, $voucherCode, $subtotal, $discount, $total, $note]);
        return (int) $pdo->lastInsertId();
    }

    public static function addItem(int $orderId, int $productId, string $name, string $cpu, string $ram, string $storage, float $price, int $quantity, float $subtotal): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_cpu, product_ram, product_storage, unit_price, quantity, subtotal)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $productId, $name, $cpu, $ram, $storage, $price, $quantity, $subtotal]);
    }

    public static function recordVoucherUsage(int $voucherId, int $userId, int $orderId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO voucher_usages (voucher_id, user_id, order_id) VALUES (?, ?, ?)");
        $stmt->execute([$voucherId, $userId, $orderId]);
    }

    public static function findById(int $orderId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetch() ?: null;
    }
}
