<?php
namespace App\Models;

use App\Core\Database;

class Payment {
    public static function create(int $orderId, float $amount, string $method, ?string $transactionCode = null, string $status = 'pending', ?string $note = null): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO payments (order_id, amount, method, transaction_code, status, note)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $amount, $method, $transactionCode, $status, $note]);
        return (int) $pdo->lastInsertId();
    }

    public static function findByOrderId(int $orderId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$orderId]);
        return $stmt->fetch() ?: null;
    }

    public static function findByTransactionCode(string $transactionCode): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_code = ?");
        $stmt->execute([$transactionCode]);
        return $stmt->fetch() ?: null;
    }

    public static function updateStatus(int $paymentId, string $status, ?string $transactionCode = null): void {
        $pdo = Database::getConnection();
        if ($transactionCode !== null) {
            $stmt = $pdo->prepare("UPDATE payments SET status = ?, transaction_code = ? WHERE id = ?");
            $stmt->execute([$status, $transactionCode, $paymentId]);
        } else {
            $stmt = $pdo->prepare("UPDATE payments SET status = ? WHERE id = ?");
            $stmt->execute([$status, $paymentId]);
        }
    }

    public static function markSuccess(int $paymentId, string $transactionCode): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE payments SET status = 'success', transaction_code = ?, paid_at = NOW() WHERE id = ?");
        $stmt->execute([$transactionCode, $paymentId]);
    }

    public static function markFailed(int $paymentId): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE payments SET status = 'failed' WHERE id = ?");
        $stmt->execute([$paymentId]);
    }
}
