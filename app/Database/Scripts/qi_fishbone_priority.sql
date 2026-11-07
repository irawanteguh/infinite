-- Tambahkan kolom prioritas penyebab Fishbone jika belum tersedia.
-- Nilai: urgent (merah), medium (kuning), low (hijau).
SET @priority_column_exists := (
    SELECT COUNT(*)
    FROM `information_schema`.`COLUMNS`
    WHERE `TABLE_SCHEMA` = DATABASE()
      AND `TABLE_NAME` = 'dt01_qi_fishbone_dt'
      AND `COLUMN_NAME` = 'priority'
);

SET @priority_column_sql := IF(
    @priority_column_exists = 0,
    'ALTER TABLE `dt01_qi_fishbone_dt` ADD COLUMN `priority` VARCHAR(10) NOT NULL DEFAULT ''medium'' AFTER `cause`',
    'SELECT 1'
);

PREPARE priority_column_stmt FROM @priority_column_sql;
EXECUTE priority_column_stmt;
DEALLOCATE PREPARE priority_column_stmt;
