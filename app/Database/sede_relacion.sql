-- Relaciona la sede de Libros Electrónicos y de Stan con la tabla `sede` (sede_id).
-- Idempotente: se puede ejecutar varias veces. No borra datos: libro.lib_sede y stan.stan_sede quedan como texto antiguo.
-- Requiere que ya existan las tablas sede, libro y stan, con las sedes registradas en `sede`
-- (los registros existentes se enlazan comparando el nombre sin importar mayúsculas: "Tarapoto" = "TARAPOTO").

SET NAMES utf8mb4;

-- 1) libro.sede_id
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'sede_id');
SET @sql := IF(@existe = 0,
  'ALTER TABLE `libro` ADD COLUMN `sede_id` INT NULL COMMENT ''sede.id'' AFTER `lib_sede`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) stan.sede_id
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stan' AND COLUMN_NAME = 'sede_id');
SET @sql := IF(@existe = 0,
  'ALTER TABLE `stan` ADD COLUMN `sede_id` INT NULL COMMENT ''sede.id'' AFTER `stan_sede`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Enlaza los registros existentes por nombre (solo los que aún no tienen sede_id)
UPDATE `libro` l
INNER JOIN `sede` s
  ON LOWER(CONVERT(s.`nombre_sede` USING utf8mb4)) COLLATE utf8mb4_general_ci
   = LOWER(CONVERT(TRIM(l.`lib_sede`) USING utf8mb4)) COLLATE utf8mb4_general_ci
SET l.`sede_id` = s.`id`
WHERE l.`sede_id` IS NULL;

UPDATE `stan` t
INNER JOIN `sede` s
  ON LOWER(CONVERT(s.`nombre_sede` USING utf8mb4)) COLLATE utf8mb4_general_ci
   = LOWER(CONVERT(TRIM(t.`stan_sede`) USING utf8mb4)) COLLATE utf8mb4_general_ci
SET t.`sede_id` = s.`id`
WHERE t.`sede_id` IS NULL;
