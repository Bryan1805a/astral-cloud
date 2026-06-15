-- ============================================================
-- Migration: Multi-Factor Authentication (TOTP)
-- Adds mfa_secret + mfa_enabled to users table
--
-- Run on existing database:
--   docker exec -i astral_db mysql -u root -p astral_cloud < sql/migration_mfa.sql
-- ============================================================

ALTER TABLE users
ADD COLUMN mfa_secret VARCHAR(32) DEFAULT NULL
AFTER verification_token;

ALTER TABLE users
ADD COLUMN mfa_enabled TINYINT(1) NOT NULL DEFAULT 0
AFTER mfa_secret;
