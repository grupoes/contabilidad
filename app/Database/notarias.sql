-- Notarías: mantenimiento en Configuración > Notarias y su uso en Libros Electrónicos.
-- Idempotente: se puede ejecutar varias veces. Busca todo por url/nombre, no por id, así que sirve en cualquier base.
-- Requiere que ya existan las tablas libro, modulos, modulos_acciones, acciones y permisos.

SET NAMES utf8mb4;

-- 1) Tabla de notarías (not_estado: 1 = activo, 2 = inactivo, 0 = eliminado)
CREATE TABLE IF NOT EXISTS `notaria` (
  `not_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `not_ruc` VARCHAR(11) NOT NULL,
  `not_razon_social` VARCHAR(200) NOT NULL,
  `not_estado` TINYINT NOT NULL DEFAULT 1 COMMENT '1 activo, 2 inactivo, 0 eliminado',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`not_id`),
  KEY `idx_notaria_ruc` (`not_ruc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Notaría elegida en cada libro (libro.lib_notaria queda como texto antiguo)
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = 'not_id');
SET @sql := IF(@existe = 0,
  'ALTER TABLE `libro` ADD COLUMN `not_id` INT NULL COMMENT ''notaria.not_id'' AFTER `lib_notaria`',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Submódulo "Notarias" dentro de Configuración (al final de sus hijos)
INSERT INTO `modulos` (`nombre`, `url`, `icono`, `orden`, `estado`, `modulo_padre`)
SELECT 'Notarias', 'configuracion/notarias', '',
       (SELECT COALESCE(MAX(m2.`orden`), 0) + 1 FROM `modulos` m2 WHERE m2.`modulo_padre` = p.`id`),
       1, p.`id`
FROM `modulos` p
WHERE p.`modulo_padre` = 0 AND p.`nombre` LIKE 'Configuraci%'
  AND NOT EXISTS (SELECT 1 FROM `modulos` x WHERE x.`url` = 'configuracion/notarias')
LIMIT 1;

-- 4) Acciones que ofrece el submódulo (aparecen en la pantalla de Permisos)
INSERT INTO `modulos_acciones` (`modulo_id`, `accion_id`)
SELECT m.`id`, a.`id`
FROM `modulos` m
INNER JOIN `acciones` a ON a.`nombre_accion` IN ('listar', 'crear', 'editar', 'eliminar')
WHERE m.`url` = 'configuracion/notarias'
  AND NOT EXISTS (
    SELECT 1 FROM `modulos_acciones` ma WHERE ma.`modulo_id` = m.`id` AND ma.`accion_id` = a.`id`
  );

-- 5) Permisos para el Super Administrador (perfil 1); el resto de perfiles se asigna desde Permisos
INSERT INTO `permisos` (`perfil_id`, `modulo_id`, `accion_id`)
SELECT 1, m.`id`, a.`id`
FROM `modulos` m
INNER JOIN `acciones` a ON a.`nombre_accion` IN ('listar', 'crear', 'editar', 'eliminar')
WHERE m.`url` = 'configuracion/notarias'
  AND NOT EXISTS (
    SELECT 1 FROM `permisos` p WHERE p.`perfil_id` = 1 AND p.`modulo_id` = m.`id` AND p.`accion_id` = a.`id`
  );
