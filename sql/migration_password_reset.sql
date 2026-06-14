-- ============================================================
-- Migration: Password Reset
-- Adds reset_token + reset_token_expires to users table
--
-- Run on existing database:
--   docker exec -i astral_db mysql -u root -p astral_cloud < sql/migration_password_reset.sql
-- ============================================================

ALTER TABLE users
ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL
AFTER verification_token;

ALTER TABLE users
ADD COLUMN reset_token_expires DATETIME DEFAULT NULL
AFTER reset_token;
