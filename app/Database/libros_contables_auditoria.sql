-- Libros electrónicos: actualización para producción.
--   1) Régimen de Renta por libro (libro.tri_id).
--   2) Auditoría: usuario y fecha de quién registró / editó / eliminó libros, impresiones y simulaciones.
-- Solo agrega las columnas que falten, se puede ejecutar varias veces y no modifica datos.
-- Requiere que ya existan las tablas libro, libro_impresion y libro_simulacion.

SET NAMES utf8mb4;

-- 1) Régimen de Renta del libro (tributo.id_tributo con id_pdt = 1)
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'tri_id');
SET @sql := IF(@existe = 0,
  'ALTER TABLE `libro` ADD COLUMN `tri_id` INT NULL COMMENT ''tributo.id_tributo (régimen de Renta, PDT RENTA)'' AFTER `tli_id`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Auditoría (usuario.id y fecha)
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'lib_user_add') = 0, 'ALTER TABLE `libro` ADD COLUMN `lib_user_add` INT NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'lib_fecha_add') = 0, 'ALTER TABLE `libro` ADD COLUMN `lib_fecha_add` DATETIME NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'lib_user_edit') = 0, 'ALTER TABLE `libro` ADD COLUMN `lib_user_edit` INT NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'lib_fecha_edit') = 0, 'ALTER TABLE `libro` ADD COLUMN `lib_fecha_edit` DATETIME NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'lib_user_delete') = 0, 'ALTER TABLE `libro` ADD COLUMN `lib_user_delete` INT NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'lib_fecha_delete') = 0, 'ALTER TABLE `libro` ADD COLUMN `lib_fecha_delete` DATETIME NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro_impresion' AND COLUMN_NAME = 'lim_user_add') = 0, 'ALTER TABLE `libro_impresion` ADD COLUMN `lim_user_add` INT NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro_impresion' AND COLUMN_NAME = 'lim_fecha_add') = 0, 'ALTER TABLE `libro_impresion` ADD COLUMN `lim_fecha_add` DATETIME NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro_impresion' AND COLUMN_NAME = 'lim_user_delete') = 0, 'ALTER TABLE `libro_impresion` ADD COLUMN `lim_user_delete` INT NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro_impresion' AND COLUMN_NAME = 'lim_fecha_delete') = 0, 'ALTER TABLE `libro_impresion` ADD COLUMN `lim_fecha_delete` DATETIME NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro_simulacion' AND COLUMN_NAME = 'lsi_user_add') = 0, 'ALTER TABLE `libro_simulacion` ADD COLUMN `lsi_user_add` INT NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro_simulacion' AND COLUMN_NAME = 'lsi_fecha_add') = 0, 'ALTER TABLE `libro_simulacion` ADD COLUMN `lsi_fecha_add` DATETIME NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro_simulacion' AND COLUMN_NAME = 'lsi_user_delete') = 0, 'ALTER TABLE `libro_simulacion` ADD COLUMN `lsi_user_delete` INT NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro_simulacion' AND COLUMN_NAME = 'lsi_fecha_delete') = 0, 'ALTER TABLE `libro_simulacion` ADD COLUMN `lsi_fecha_delete` DATETIME NULL', 'SELECT 1'); PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
