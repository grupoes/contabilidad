<?php
if (!function_exists('tiene_permiso')) {
    function tiene_permiso($modulo, $accion, $menu)
    {
        foreach ($menu as $grupo) {
            foreach ($grupo['hijos'] as $hijo) {
                if ($hijo['nombre'] == $modulo) {
                    return in_array($accion, $hijo['acciones']);
                }
            }
        }
        return false;
    }
}
