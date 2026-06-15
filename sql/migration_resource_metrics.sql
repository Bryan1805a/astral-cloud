-- ============================================================
-- Migration: Resource Metrics
-- Stores periodic CPU/RAM/disk/network snapshots per service
--
-- Run on existing database:
--   docker exec -i astral_db mysql -u root -p astral_cloud < sql/migration_resource_metrics.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS resource_metrics (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id    INT UNSIGNED  NOT NULL,
    cpu_load      DECIMAL(6,2)  DEFAULT NULL,
    ram_used_mb   INT UNSIGNED  DEFAULT NULL,
    ram_total_mb  INT UNSIGNED  DEFAULT NULL,
    disk_used_gb  DECIMAL(8,2)  DEFAULT NULL,
    disk_total_gb DECIMAL(8,2)  DEFAULT NULL,
    net_rx_bytes  BIGINT UNSIGNED DEFAULT 0,
    net_tx_bytes  BIGINT UNSIGNED DEFAULT 0,
    collected_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_service_time (service_id, collected_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
