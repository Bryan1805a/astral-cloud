<?php

class Voucher {
    // Find voucher by Code
    public static function findByCode(string $code): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = :code AND is_active = 1");
        $stmt->execute(['code' => $code]);
        return $stmt->fetch() ?: null;
    }

    // Get list of all vouchers
    public static function getAll(): array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM vouchers ORDER BY created_at DESC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add new voucher and save to datase
    public static function create(array $data): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO vouchers 
            (code, description, discount_type, discount_value, min_order_value, max_discount, quantity, applicable_tier, expiry_date, is_active)
            VALUES 
            (:code, :description, :discount_type, :discount_value, :min_order_value, :max_discount, :quantity, :applicable_tier, :expiry_date, :is_active)
        ");

        $stmt->execute($data);
    }
}
