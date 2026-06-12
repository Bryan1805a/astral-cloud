-- ============================================================
-- Migration: Add Guacamole connection ID to services table
-- Run this if you already have a running database:
--   docker exec -i astral_db mysql -u root -p astral_cloud < sql/migration_guacamole.sql
-- ============================================================

ALTER TABLE services
ADD COLUMN guacamole_connection_id INT UNSIGNED DEFAULT NULL
COMMENT 'Apache Guacamole connection ID for web console'
AFTER os;
