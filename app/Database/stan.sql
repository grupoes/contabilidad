-- Stan (por sede): mantenimiento en Configuración > Stan y su uso en Libros Electrónicos.
-- Script completo e idempotente: se puede ejecutar varias veces. Busca todo por url/nombre, no por id.
-- Requiere que ya existan las tablas sede, libro, modulos, modulos_acciones, acciones y permisos.

SET NAMES utf8mb4;

-- 1) Tabla de stan, relacionada con la tabla sede (stan.sede_id -> sede.id)
--    stan_sede queda como texto de respaldo; stan_estado: 1 = activo, 2 = inactivo, 0 = eliminado
CREATE TABLE IF NOT EXISTS `stan` (
  `stan_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stan_sede` VARCHAR(50) NOT NULL,
  `sede_id` INT NULL COMMENT 'sede.id',
  `stan_numero` VARCHAR(50) NOT NULL,
  `stan_estado` TINYINT NOT NULL DEFAULT 1 COMMENT '1 activo, 2 inactivo, 0 eliminado',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`stan_id`),
  KEY `idx_stan_sede` (`stan_sede`),
  KEY `idx_stan_sede_id` (`sede_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Si la tabla stan ya existía sin sede_id, se agrega y se enlaza por nombre con la tabla sede
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stan' AND COLUMN_NAME = 'sede_id');
SET @sql := IF(@existe = 0,
  'ALTER TABLE `stan` ADD COLUMN `sede_id` INT NULL COMMENT ''sede.id'' AFTER `stan_sede`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `stan` t
INNER JOIN `sede` s
  ON LOWER(CONVERT(s.`nombre_sede` USING utf8mb4)) COLLATE utf8mb4_general_ci
   = LOWER(CONVERT(TRIM(t.`stan_sede`) USING utf8mb4)) COLLATE utf8mb4_general_ci
SET t.`sede_id` = s.`id`
WHERE t.`sede_id` IS NULL;

-- 2) Stan elegido en cada libro (libro.lib_stan queda como texto antiguo)
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'stan_id');
SET @sql := IF(@existe = 0,
  'ALTER TABLE `libro` ADD COLUMN `stan_id` INT NULL COMMENT ''stan.stan_id'' AFTER `lib_stan`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Submódulo "Stan" dentro de Configuración (al final de sus hijos)
INSERT INTO `modulos` (`nombre`, `url`, `icono`, `orden`, `estado`, `modulo_padre`)
SELECT 'Stan', 'configuracion/stan', '',
       (SELECT COALESCE(MAX(m2.`orden`), 0) + 1 FROM `modulos` m2 WHERE m2.`modulo_padre` = p.`id`),
       1, p.`id`
FROM `modulos` p
WHERE p.`modulo_padre` = 0 AND p.`nombre` LIKE 'Configuraci%'
  AND NOT EXISTS (SELECT 1 FROM `modulos` x WHERE x.`url` = 'configuracion/stan')
LIMIT 1;

-- 4) Acciones que ofrece el submódulo (aparecen en la pantalla de Permisos)
INSERT INTO `modulos_acciones` (`modulo_id`, `accion_id`)
SELECT m.`id`, a.`id`
FROM `modulos` m
INNER JOIN `acciones` a ON a.`nombre_accion` IN ('listar', 'crear', 'editar', 'eliminar')
WHERE m.`url` = 'configuracion/stan'
  AND NOT EXISTS (
    SELECT 1 FROM `modulos_acciones` ma WHERE ma.`modulo_id` = m.`id` AND ma.`accion_id` = a.`id`
  );

-- 5) Permisos para el Super Administrador (perfil 1); el resto de perfiles se asigna desde Permisos
INSERT INTO `permisos` (`perfil_id`, `modulo_id`, `accion_id`)
SELECT 1, m.`id`, a.`id`
FROM `modulos` m
INNER JOIN `acciones` a ON a.`nombre_accion` IN ('listar', 'crear', 'editar', 'eliminar')
WHERE m.`url` = 'configuracion/stan'
  AND NOT EXISTS (
    SELECT 1 FROM `permisos` p WHERE p.`perfil_id` = 1 AND p.`modulo_id` = m.`id` AND p.`accion_id` = a.`id`
  );
