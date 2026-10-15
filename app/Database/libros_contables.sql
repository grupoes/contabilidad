-- Libros contables dentro de Contabilidad > Libros Electrónicos (modulos.id = 32, url 'libros-electronicos')
-- Script completo e idempotente: se puede ejecutar varias veces en la BD del servidor sin duplicar nada.
-- Compatible con MySQL 5.7/8 y MariaDB. No borra ni modifica datos existentes.

SET NAMES utf8mb4;

-- 1) Tipos de libro ---------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tipo_libro` (
  `tli_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tli_nombre` VARCHAR(150) NOT NULL,
  `tli_estado` TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (`tli_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tipo_libro` (`tli_nombre`)
SELECT v.`n`
FROM (
  SELECT 'Libro Diario' AS `n`
  UNION ALL SELECT 'Libro Diario de Formato Simplificado'
  UNION ALL SELECT 'Registro de Inventario Permanente en Unidades Físicas'
  UNION ALL SELECT 'Registro de Inventario Permanente Valorizado'
  UNION ALL SELECT 'Libro Mayor'
  UNION ALL SELECT 'Libro de Inventarios y Balances'
  UNION ALL SELECT 'Libro Caja y Bancos'
  UNION ALL SELECT 'Registro de Compras'
  UNION ALL SELECT 'Registro de Ventas'
  UNION ALL SELECT 'Registro de Ventas e Ingresos'
  UNION ALL SELECT 'Registro de Activos Fijos'
  UNION ALL SELECT 'Libro de Planillas'
  UNION ALL SELECT 'Libro de Actas'
  UNION ALL SELECT 'Registro de Costos'
) v
WHERE NOT EXISTS (SELECT 1 FROM `tipo_libro` t WHERE t.`tli_nombre` = v.`n`);

-- 2) Libros -----------------------------------------------------------------------------------------
-- tri_id = régimen de Renta del libro (tributo.id_tributo con id_pdt = 1: general, especial, amazonia, mype, agrario).
-- Si es NULL se usa el régimen que el contribuyente tiene configurado en configuracion_notificacion.
CREATE TABLE IF NOT EXISTS `libro` (
  `lib_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `con_id` INT NOT NULL COMMENT 'contribuyentes.id',
  `tli_id` INT UNSIGNED NOT NULL,
  `tri_id` INT NULL COMMENT 'tributo.id_tributo (régimen de Renta, PDT RENTA)',
  `lib_numero` VARCHAR(30) NULL,
  `lib_sede` VARCHAR(50) NOT NULL DEFAULT 'Tarapoto',
  `lib_notaria` VARCHAR(150) NULL,
  `lib_fecha_legalizacion` DATE NULL,
  `lib_kardex` VARCHAR(50) NULL,
  `lib_stan` VARCHAR(50) NULL,
  `lib_folio_inicial` INT NOT NULL DEFAULT 1,
  `lib_folio_final` INT NOT NULL DEFAULT 1,
  `lib_total_hojas` INT NOT NULL DEFAULT 0,
  `lib_minimo` INT NOT NULL DEFAULT 100,
  `lib_observacion` VARCHAR(255) NULL,
  `lib_estado` TINYINT NOT NULL DEFAULT 1,
  `lib_user_add` INT NULL COMMENT 'usuario.id que registró',
  `lib_fecha_add` DATETIME NULL,
  `lib_user_edit` INT NULL COMMENT 'usuario.id de la última edición',
  `lib_fecha_edit` DATETIME NULL,
  `lib_user_delete` INT NULL COMMENT 'usuario.id que eliminó',
  `lib_fecha_delete` DATETIME NULL,
  PRIMARY KEY (`lib_id`),
  KEY `idx_libro_con` (`con_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Si la tabla `libro` ya existía de una versión anterior, agrega la columna tri_id solo si falta.
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'tri_id');
SET @sql := IF(@existe = 0,
  'ALTER TABLE `libro` ADD COLUMN `tri_id` INT NULL COMMENT ''tributo.id_tributo (régimen de Renta, PDT RENTA)'' AFTER `tli_id`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Historial de impresiones -----------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `libro_impresion` (
  `lim_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lib_id` INT UNSIGNED NOT NULL,
  `lim_folio_desde` INT NOT NULL,
  `lim_folio_hasta` INT NOT NULL,
  `lim_periodo_desde` VARCHAR(7) NULL COMMENT 'YYYY-MM (MM 00=Apertura, 13/14=Cierre)',
  `lim_periodo_hasta` VARCHAR(7) NULL,
  `lim_estado` TINYINT NOT NULL DEFAULT 1,
  `lim_user_add` INT NULL COMMENT 'usuario.id que registró',
  `lim_fecha_add` DATETIME NULL,
  `lim_user_delete` INT NULL COMMENT 'usuario.id que eliminó',
  `lim_fecha_delete` DATETIME NULL,
  PRIMARY KEY (`lim_id`),
  KEY `idx_lim_lib` (`lib_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Simulador --------------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `libro_simulacion` (
  `lsi_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lib_id` INT UNSIGNED NOT NULL,
  `lsi_periodo` VARCHAR(7) NOT NULL,
  `lsi_hojas` INT NOT NULL,
  `lsi_estado` TINYINT NOT NULL DEFAULT 1,
  `lsi_user_add` INT NULL COMMENT 'usuario.id que registró',
  `lsi_fecha_add` DATETIME NULL,
  `lsi_user_delete` INT NULL COMMENT 'usuario.id que eliminó',
  `lsi_fecha_delete` DATETIME NULL,
  PRIMARY KEY (`lsi_id`),
  KEY `idx_lsi_lib` (`lib_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6) Auditoría: quién y cuándo registró / editó / eliminó (para tablas creadas con una versión anterior).
-- Agrega cada columna solo si falta; no modifica datos.
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

-- 5) Permisos ---------------------------------------------------------------------------------------
-- Requiere que ya exista el módulo "Libros Electrónicos" (modulos.id = 32) y las acciones crear/editar/eliminar.
-- Se agregan para el perfil administrador (1); el resto de perfiles se asigna desde Permisos.
INSERT INTO `permisos` (`perfil_id`, `modulo_id`, `accion_id`)
SELECT 1, 32, a.`id` FROM `acciones` a
WHERE a.`nombre_accion` IN ('crear', 'editar', 'eliminar')
  AND NOT EXISTS (
    SELECT 1 FROM `permisos` p WHERE p.`perfil_id` = 1 AND p.`modulo_id` = 32 AND p.`accion_id` = a.`id`
  );
