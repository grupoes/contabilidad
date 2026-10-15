-- Libros electrónicos: acciones de permisos por módulo (modulos.url = 'libros-electronicos').
-- Se pueden asignar desde Permisos: ver detalle, crear, editar, eliminar, registrar impresion, simular y reponer hojas
-- (listar controla la entrada al módulo).
-- Idempotente: se puede ejecutar varias veces. Busca todo por nombre/url, no por id, así que sirve en cualquier base.
-- No quita permisos: solo agrega los que faltan para que ningún perfil pierda lo que ya podía hacer.

SET NAMES utf8mb4;

-- 1) Acciones nuevas (las demás ya existen en `acciones`)
INSERT INTO `acciones` (`nombre_accion`, `estado`)
SELECT v.`n`, 1
FROM (
  SELECT 'registrar impresion' AS `n`
  UNION ALL SELECT 'simular'
  UNION ALL SELECT 'reponer hojas'
) v
WHERE NOT EXISTS (SELECT 1 FROM `acciones` a WHERE a.`nombre_accion` = v.`n`);

-- 2) Acciones que ofrece el módulo (son las que aparecen en la pantalla de Permisos)
INSERT INTO `modulos_acciones` (`modulo_id`, `accion_id`)
SELECT m.`id`, a.`id`
FROM `modulos` m
INNER JOIN `acciones` a ON a.`nombre_accion` IN ('listar', 'ver detalle', 'crear', 'editar', 'eliminar', 'registrar impresion', 'simular', 'reponer hojas')
WHERE m.`url` = 'libros-electronicos'
  AND NOT EXISTS (
    SELECT 1 FROM `modulos_acciones` ma WHERE ma.`modulo_id` = m.`id` AND ma.`accion_id` = a.`id`
  );

-- 3) Conserva lo que cada perfil ya podía hacer
--    - quien podía "listar" (y abría el detalle) recibe "ver detalle"
INSERT INTO `permisos` (`perfil_id`, `modulo_id`, `accion_id`)
SELECT p.`perfil_id`, p.`modulo_id`, nueva.`id`
FROM `permisos` p
INNER JOIN `modulos` m ON m.`id` = p.`modulo_id` AND m.`url` = 'libros-electronicos'
INNER JOIN `acciones` base ON base.`id` = p.`accion_id` AND base.`nombre_accion` = 'listar'
INNER JOIN `acciones` nueva ON nueva.`nombre_accion` = 'ver detalle'
WHERE NOT EXISTS (
  SELECT 1 FROM `permisos` x WHERE x.`perfil_id` = p.`perfil_id` AND x.`modulo_id` = p.`modulo_id` AND x.`accion_id` = nueva.`id`
);

--    - quien podía "editar" (antes controlaba impresiones, simulaciones y reponer) recibe las 3 acciones nuevas
INSERT INTO `permisos` (`perfil_id`, `modulo_id`, `accion_id`)
SELECT p.`perfil_id`, p.`modulo_id`, nueva.`id`
FROM `permisos` p
INNER JOIN `modulos` m ON m.`id` = p.`modulo_id` AND m.`url` = 'libros-electronicos'
INNER JOIN `acciones` base ON base.`id` = p.`accion_id` AND base.`nombre_accion` = 'editar'
INNER JOIN `acciones` nueva ON nueva.`nombre_accion` IN ('registrar impresion', 'simular', 'reponer hojas')
WHERE NOT EXISTS (
  SELECT 1 FROM `permisos` x WHERE x.`perfil_id` = p.`perfil_id` AND x.`modulo_id` = p.`modulo_id` AND x.`accion_id` = nueva.`id`
);
