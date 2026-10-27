-- Libros electrónicos: campo "Tipo de hoja" del libro (libro.lib_tipo_hoja).
-- Valores: 'manual', 'computarizado', 'electronico'. Los libros existentes quedan sin valor (NULL) hasta que se editen.
-- Idempotente: se puede ejecutar varias veces. Solo agrega la columna si falta; no modifica datos.
-- Requiere que ya exista la tabla libro.

SET NAMES utf8mb4;

SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'lib_tipo_hoja');
SET @sql := IF(@existe = 0,
  'ALTER TABLE `libro` ADD COLUMN `lib_tipo_hoja` VARCHAR(20) NULL COMMENT ''manual, computarizado o electronico'' AFTER `tli_id`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
