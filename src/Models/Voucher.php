<?php

class Voucher {
    public static function findByCode(string $code): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE code = :code AND is_active = 1");
        $stmt->execute(['code' => $code]);
        return $stmt->fetch() ?: null;
    }
}
