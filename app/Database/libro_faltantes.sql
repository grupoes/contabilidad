-- Libros electrónicos: libros importados "Por completar" (libro.lib_faltantes) y tipos de libro adicionales.
-- lib_faltantes guarda, como texto, los datos que le faltan al libro (por ejemplo "Sede, N° de kardex, N° de stan").
-- NULL = el libro está completo. Se limpia solo cuando el libro se guarda completo desde Editar.
-- Idempotente: se puede ejecutar varias veces. No modifica datos existentes.
-- Requiere que ya existan las tablas libro y tipo_libro.

SET NAMES utf8mb4;

-- 1) Columna libro.lib_faltantes
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'lib_faltantes');
SET @sql := IF(@existe = 0,
  'ALTER TABLE `libro` ADD COLUMN `lib_faltantes` VARCHAR(255) NULL COMMENT ''Datos por completar (importación); NULL = completo'' AFTER `lib_estado`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Tipos de libro que usan los clientes y no estaban en el catálogo
INSERT INTO `tipo_libro` (`tli_nombre`)
SELECT v.`n`
FROM (
  SELECT 'Kardex' AS `n`
  UNION ALL SELECT 'Libro de Matrícula de Acciones'
) v
WHERE NOT EXISTS (SELECT 1 FROM `tipo_libro` t WHERE t.`tli_nombre` = v.`n`);
