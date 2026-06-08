<?php

class AuditLog {
    public static function log(string $action, string $entityType, ?int $entityId, string $description, ?array $oldValues = null, ?array $newValues = null): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, old_values, new_values)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION["user_id"] ?? null,
            $action,
            $entityType,
            $entityId,
            $description,
            $_SERVER["REMOTE_ADDR"] ?? null,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
        ]);
    }

    public static function logSystem(string $action, string $entityType, ?int $entityId, string $description, ?array $oldValues = null, ?array $newValues = null): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, entity_type, entity_id, description, ip_address, old_values, new_values)
            VALUES (NULL, ?, ?, ?, ?, NULL, ?, ?)
        ");
        $stmt->execute([
            $action,
            $entityType,
            $entityId,
            $description,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
        ]);
    }

    public static function getAll(int $page = 1, int $perPage = 50, ?string $actionFilter = null, ?string $entityFilter = null): array {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];

        if ($actionFilter) {
            $where[] = "a.action = ?";
            $params[] = $actionFilter;
        }
        if ($entityFilter) {
            $where[] = "a.entity_type = ?";
            $params[] = $entityFilter;
        }

        $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT a.*, u.name AS user_name
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            {$whereClause}
            ORDER BY a.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getDistinctActions(): array {
        $pdo = Database::getConnection();
        return $pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getDistinctEntityTypes(): array {
        $pdo = Database::getConnection();
        return $pdo->query("SELECT DISTINCT entity_type FROM audit_logs ORDER BY entity_type")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getTotalCount(?string $actionFilter = null, ?string $entityFilter = null): int {
        $pdo = Database::getConnection();
        $where = [];
        $params = [];

        if ($actionFilter) {
            $where[] = "action = ?";
            $params[] = $actionFilter;
        }
        if ($entityFilter) {
            $where[] = "entity_type = ?";
            $params[] = $entityFilter;
        }

        $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs {$whereClause}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
