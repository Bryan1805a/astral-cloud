<?php

/**
 * RateLimiter — IP-based brute-force protection
 *
 * Tracks login/register attempts in the rate_limits table.
 * Identifies clients by SHA1(real IP || action), handling proxies
 * via X-Forwarded-For / X-Real-IP headers.
 *
 * Config:
 *   Login:    5 attempts / 15 min window, 15 min block
 *   Register: 3 attempts / 60 min window, 60 min block
 *
 * Call check() before processing, record() on failure, clear() on success.
 * Cron calls pruneExpired() to keep the table small.
 */
class RateLimiter {
    private const LOGIN_MAX_ATTEMPTS     = 5;
    private const LOGIN_WINDOW_MINUTES   = 15;
    private const LOGIN_BLOCK_MINUTES    = 15;

    private const REGISTER_MAX_ATTEMPTS   = 3;
    private const REGISTER_WINDOW_MINUTES = 60;
    private const REGISTER_BLOCK_MINUTES  = 60;

    private static function getClientIp(): string {
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP'];
        foreach ($headers as $header) {
            $value = $_SERVER[$header] ?? '';
            if ($value) {
                $ips = array_map('trim', explode(',', $value));
                $ip = $ips[0];
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private static function getConfig(string $action): array {
        if ($action === 'login') {
            return [
                'max_attempts'  => self::LOGIN_MAX_ATTEMPTS,
                'window_minutes' => self::LOGIN_WINDOW_MINUTES,
                'block_minutes'  => self::LOGIN_BLOCK_MINUTES,
            ];
        }
        return [
            'max_attempts'   => self::REGISTER_MAX_ATTEMPTS,
            'window_minutes' => self::REGISTER_WINDOW_MINUTES,
            'block_minutes'  => self::REGISTER_BLOCK_MINUTES,
        ];
    }

    public static function check(string $action): ?string {
        $config   = self::getConfig($action);
        $ip       = self::getClientIp();
        $ident    = sha1($ip . '|' . $action);
        $pdo      = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT attempts, blocked_until, updated_at
            FROM rate_limits
            WHERE identifier = ?
        ");
        $stmt->execute([$ident]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        if ($row['blocked_until'] && strtotime($row['blocked_until']) > time()) {
            $remainingSeconds = max(0, strtotime($row['blocked_until']) - time());
            $remainingMinutes = (int) ceil($remainingSeconds / 60);
            return $action === 'login'
                ? "Too many {$action} attempts. Please try again in {$remainingMinutes} minute(s)."
                : "Too many {$action} attempts. Please try again in {$remainingMinutes} minute(s).";
        }

        $windowExpired = strtotime($row['updated_at']) + ($config['window_minutes'] * 60) < time();

        if ($windowExpired) {
            $pdo->prepare("DELETE FROM rate_limits WHERE identifier = ?")->execute([$ident]);
            return null;
        }

        if ($row['attempts'] >= $config['max_attempts']) {
            $blockUntil = date('Y-m-d H:i:s', time() + $config['block_minutes'] * 60);
            $pdo->prepare("UPDATE rate_limits SET blocked_until = ? WHERE identifier = ?")
                ->execute([$blockUntil, $ident]);
            return $action === 'login'
                ? "Too many {$action} attempts. Please try again in {$config['block_minutes']} minute(s)."
                : "Too many {$action} attempts. Please try again in {$config['block_minutes']} minute(s).";
        }

        return null;
    }

    public static function record(string $action): void {
        $ip    = self::getClientIp();
        $ident = sha1($ip . '|' . $action);
        $pdo   = Database::getConnection();

        $stmt = $pdo->prepare("
            INSERT INTO rate_limits (identifier, action, attempts, created_at, updated_at)
            VALUES (?, ?, 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                attempts    = attempts + 1,
                updated_at  = NOW()
        ");
        $stmt->execute([$ident, $action]);
    }

    public static function clear(string $action): void {
        $ip    = self::getClientIp();
        $ident = sha1($ip . '|' . $action);
        Database::getConnection()
            ->prepare("DELETE FROM rate_limits WHERE identifier = ?")
            ->execute([$ident]);
    }

    public static function pruneExpired(): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            DELETE FROM rate_limits
            WHERE (blocked_until IS NULL AND updated_at < DATE_SUB(NOW(), INTERVAL 2 HOUR))
               OR (blocked_until IS NOT NULL AND blocked_until < NOW() - INTERVAL 1 DAY)
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
