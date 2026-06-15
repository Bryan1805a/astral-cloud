-- ============================================================
-- Migration: Rate Limiting
-- Adds rate_limits table for login/register brute-force protection
--
-- Run on existing database:
--   docker exec -i astral_db mysql -u root -p astral_cloud < sql/migration_rate_limits.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS rate_limits (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier      VARCHAR(64)  NOT NULL,          -- sha1(ip||action)
    action          VARCHAR(32)  NOT NULL,          -- 'login' or 'register'
    attempts        INT UNSIGNED NOT NULL DEFAULT 1,
    blocked_until   DATETIME     DEFAULT NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_identifier (identifier),
    INDEX idx_blocked (blocked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
