<?php
namespace App\Models;

use App\Core\Database;

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
        return $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
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

    // Count how many times a user has used a specific voucher
    public static function getUsageCount(int $voucherId, int $userId): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM voucher_usages WHERE voucher_id = ? AND user_id = ?");
        $stmt->execute([$voucherId, $userId]);
        return (int) $stmt->fetchColumn();
    }

    // Change voucher status
    // turn on / off
    public static function toggleActive(int $id): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE vouchers SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
    }
}
