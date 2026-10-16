-- Libros electrónicos: campo "Observación" en Nueva impresión (libro_impresion.lim_observacion).
-- Idempotente: se puede ejecutar varias veces. Solo agrega la columna si falta; no modifica datos.
-- Requiere que ya exista la tabla libro_impresion.

SET NAMES utf8mb4;

SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro_impresion' AND COLUMN_NAME = 'lim_observacion');
SET @sql := IF(@existe = 0,
  'ALTER TABLE `libro_impresion` ADD COLUMN `lim_observacion` VARCHAR(255) NULL COMMENT ''Observación de la impresión'' AFTER `lim_periodo_hasta`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
