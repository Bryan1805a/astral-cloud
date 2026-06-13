-- ============================================================
-- Migration: Replace Guacamole with ttyd
--
-- Run this on an existing database:
--   docker exec -i astral_db mysql -u root -p astral_cloud < sql/migration_ttyd.sql
-- ============================================================

-- Rename guacamole_connection_id to console_port
ALTER TABLE services
CHANGE COLUMN guacamole_connection_id console_port INT UNSIGNED DEFAULT NULL
COMMENT 'ttyd port for web console';

-- Add provisioning progress column
ALTER TABLE services
ADD COLUMN provisioning_status VARCHAR(50) NOT NULL DEFAULT 'pending'
COMMENT 'creating_vm | booting | waiting_ip | preparing_console | ready'
AFTER status;
