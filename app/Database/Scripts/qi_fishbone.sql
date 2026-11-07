-- Jalankan satu kali pada database aplikasi Infinite V2.
-- Skrip ini aman untuk tabel yang belum ada, dan menambahkan kolom relasi
-- indikator jika header Fishbone sudah dibuat oleh migrasi awal.

CREATE TABLE IF NOT EXISTS `dt01_qi_fishbone_hd` (
    `fishbone_id` VARCHAR(36) NOT NULL,
    `group_id` VARCHAR(36) NOT NULL,
    `org_id` VARCHAR(36) NOT NULL,
    `department_id` VARCHAR(36) NOT NULL,
    `periode_id` VARCHAR(36) NULL,
    `indikator_ref_id` VARCHAR(36) NULL,
    `problem` VARCHAR(500) NOT NULL,
    `active` CHAR(1) NOT NULL DEFAULT '1',
    `created_by` VARCHAR(36) NULL,
    `created_date` DATETIME NULL,
    `last_update_by` VARCHAR(36) NULL,
    `last_update_date` DATETIME NULL,
    PRIMARY KEY (`fishbone_id`),
    KEY `idx_qi_fishbone_hd_scope` (`group_id`, `org_id`, `active`),
    KEY `idx_qi_fishbone_hd_indicator` (`indikator_ref_id`)
);

CREATE TABLE IF NOT EXISTS `dt01_qi_fishbone_dt` (
    `cause_id` VARCHAR(36) NOT NULL,
    `fishbone_id` VARCHAR(36) NOT NULL,
    `category` VARCHAR(60) NOT NULL,
    `cause` VARCHAR(500) NOT NULL,
    `priority` VARCHAR(10) NOT NULL DEFAULT 'medium',
    `active` CHAR(1) NOT NULL DEFAULT '1',
    `created_by` VARCHAR(36) NULL,
    `created_date` DATETIME NULL,
    `last_update_by` VARCHAR(36) NULL,
    `last_update_date` DATETIME NULL,
    PRIMARY KEY (`cause_id`),
    KEY `idx_qi_fishbone_dt_header` (`fishbone_id`, `active`)
);

-- Mendukung header yang sudah ada dari migrasi 2026-11-07-000001,
-- sebelum kolom relasi indikator ditambahkan.
SET @fishbone_indicator_column_exists := (
    SELECT COUNT(*)
    FROM `information_schema`.`COLUMNS`
    WHERE `TABLE_SCHEMA` = DATABASE()
      AND `TABLE_NAME` = 'dt01_qi_fishbone_hd'
      AND `COLUMN_NAME` = 'indikator_ref_id'
);

SET @fishbone_indicator_alter := IF(
    @fishbone_indicator_column_exists = 0,
    'ALTER TABLE `dt01_qi_fishbone_hd` ADD COLUMN `indikator_ref_id` VARCHAR(36) NULL AFTER `periode_id`',
    'SELECT 1'
);

PREPARE fishbone_indicator_stmt FROM @fishbone_indicator_alter;
EXECUTE fishbone_indicator_stmt;
DEALLOCATE PREPARE fishbone_indicator_stmt;

-- Tambahkan indeks relasi jika header sudah dibuat oleh migrasi awal.
SET @fishbone_indicator_index_exists := (
    SELECT COUNT(*)
    FROM `information_schema`.`STATISTICS`
    WHERE `TABLE_SCHEMA` = DATABASE()
      AND `TABLE_NAME` = 'dt01_qi_fishbone_hd'
      AND `INDEX_NAME` = 'idx_qi_fishbone_hd_indicator'
);

SET @fishbone_indicator_index_sql := IF(
    @fishbone_indicator_index_exists = 0,
    'CREATE INDEX `idx_qi_fishbone_hd_indicator` ON `dt01_qi_fishbone_hd` (`indikator_ref_id`)',
    'SELECT 1'
);

PREPARE fishbone_indicator_index_stmt FROM @fishbone_indicator_index_sql;
EXECUTE fishbone_indicator_index_stmt;
DEALLOCATE PREPARE fishbone_indicator_index_stmt;

-- Tambahkan prioritas default Sedang pada tabel penyebab yang sudah ada.
SET @fishbone_priority_column_exists := (
    SELECT COUNT(*)
    FROM `information_schema`.`COLUMNS`
    WHERE `TABLE_SCHEMA` = DATABASE()
      AND `TABLE_NAME` = 'dt01_qi_fishbone_dt'
      AND `COLUMN_NAME` = 'priority'
);

SET @fishbone_priority_alter := IF(
    @fishbone_priority_column_exists = 0,
    'ALTER TABLE `dt01_qi_fishbone_dt` ADD COLUMN `priority` VARCHAR(10) NOT NULL DEFAULT ''medium'' AFTER `cause`',
    'SELECT 1'
);

PREPARE fishbone_priority_stmt FROM @fishbone_priority_alter;
EXECUTE fishbone_priority_stmt;
DEALLOCATE PREPARE fishbone_priority_stmt;
