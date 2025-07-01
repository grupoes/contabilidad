<?php

namespace App\Controllers;

class ventas extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function buscarPorRuc($ruc)
    {
        $db = \Config\Database::connect('restaurant');

        // 1. Traer los esquemas válidos
        $query = $db->query("SELECT schema_name FROM information_schema.schemata WHERE schema_name NOT IN ('pg_catalog', 'pg_toast', 'information_schema', '_generic', 'esrestaurant', 'public') AND schema_name NOT LIKE '%\\_data%' ESCAPE '\\' ");

        $schemas = $query->getResultArray();

        // 2. Buscar en cada tabla empresa dentro del esquema
        foreach ($schemas as $schema) {
            $schemaName = $schema['schema_name'];

            try {
                $rpta = $db->query("
                SELECT empr_id, empr_ruc, empr_razon_social FROM {$schemaName}.empresa WHERE empr_ruc = ?", [$ruc])->getRow();

                if ($rpta) {

                    return view('reportes/reporte_ventas_restaurante', compact('schemaName', 'rpta'));
                }
            } catch (\Throwable $e) {
                // Puede que no exista la tabla empresa, o haya error, así que lo ignoramos
                continue;
            }
        }

        return view('reportes/error', compact('ruc'));
    }

    public function sucursales()
    {
        $shema = $this->request->getPost('shema');
        $id = $this->request->getPost('idempresa');

        $db = \Config\Database::connect('restaurant');

        $query = $db->query("SELECT sede_id, sede_descripcion FROM {$shema}.sede WHERE empr_id = $id ORDER BY sede_id ASC");

        $sedes = $query->getResultArray();

        return $this->response->setJSON($sedes);
    }

    public function ventaDetallada()
    {
        $db = \Config\Database::connect('restaurant');

        $sucursal = $this->request->getPost('sucursal');
        $fecha_inicio = $this->request->getPost('fecha_inicio');
        $fecha_fin = $this->request->getPost('fecha_fin');
        $cuenta = $this->request->getPost('cuenta');
        $glosa = $this->request->getPost('glosa');
        $ruc = $this->request->getPost('ruc');
        $shema = $this->request->getPost('shema');

        if ($sucursal == 0) {
            $sqlsucursal = "";
        } else {
            $sqlsucursal = " AND cs.sede_id = $sucursal ";
        }

        $query = $db->query("SELECT * FROM (
            SELECT 
                v.vent_id as id, 
                TO_CHAR(v.vent_fecha, 'DD/MM/YYYY') as fecha, 
                'S' as tipo_moneda, 
                tc.tico_descripcion, 
                CONCAT(v.vent_serie,'-',v.vent_numero) as numero_documento, 
                c.clie_numero_documento, 
                c.clie_nombre_razon_social, 
                v.vent_total_exonerado as total_exonerado, 
                v.vent_total_gravado as total_gravado, 
                v.vent_total_inafecto as total_inafecto, 
                v.vent_subtotal as subtotal, 
                v.vent_total_igv as total_igv, 
                v.vent_total_icbper as total_icbper, 
                v.vent_total as total, 
                v.vent_homologacion_estado as homologacion_estado, 
                cs.sede_id, 
                cs.tico_id,
                v.vent_estado as estado,
                '' as referencia,
                '' as fecha_referencia,
                'venta' as origen
            FROM {$shema}.venta v 
            INNER JOIN {$shema}.cliente c ON c.clie_id = v.clie_id 
            INNER JOIN {$shema}.comprobante_sede cs ON cs.cose_id = v.cose_id 
            INNER JOIN {$shema}.tipo_comprobante tc ON tc.tico_id = cs.tico_id 
            WHERE cs.tico_id IN(1, 2, 3, 4) 
            AND v.vent_fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' $sqlsucursal
            AND v.vent_tipo_envio = 'PRODUCCION'

            UNION ALL

            SELECT 
                nc.nocv_id as id, 
                TO_CHAR(nc.nocv_fecha, 'DD/MM/YYYY') as fecha, 
                'S' as tipo_moneda, 
                tc.tico_descripcion, 
                CONCAT(nc.nocv_serie,'-',nc.nocv_numero) as numero_documento, 
                c.clie_numero_documento, 
                c.clie_nombre_razon_social, 
                nc.nocv_total_exonerado as total_exonerado, 
                nc.nocv_total_gravado as total_gravado, 
                nc.nocv_total_inafecto as total_inafecto, 
                nc.nocv_subtotal as subtotal, 
                nc.nocv_total_igv as total_igv, 
                nc.nocv_total_icbper as total_icbper, 
                nc.nocv_total as total, 
                nc.nocv_homologacion_estado as homologacion_estado, 
                cs.sede_id,
                cs.tico_id,
                nc.nocv_estado as estado,
                CONCAT(nc.nocv_modifica_serie, '-', nc.nocv_modifica_numero) as referencia,
                TO_CHAR(v.vent_fecha, 'DD/MM/YYYY') as fecha_referencia,
                'nota_credito' as origen
            FROM {$shema}.nota_credito_venta nc 
            INNER JOIN {$shema}.cliente c ON c.clie_id = nc.clie_id 
            INNER JOIN {$shema}.comprobante_sede cs ON cs.cose_id = nc.cose_id 
            INNER JOIN {$shema}.tipo_comprobante tc ON tc.tico_id = cs.tico_id 
            INNER JOIN {$shema}.venta v ON v.vent_id = nc.vent_id
            WHERE nc.nocv_fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' $sqlsucursal
            AND nc.nocv_tipo_envio = 'PRODUCCION'
        ) AS subconsulta

        ORDER BY 
            CASE origen 
                WHEN 'venta' THEN 1 
                WHEN 'nota_credito' THEN 2 
            END,
            sede_id ASC, 
            fecha ASC, 
            numero_documento ASC;
        ");

        /*$query = $db->query("SELECT nc.nocv_id as id, TO_CHAR(nc.nocv_fecha, 'DD/MM/YYYY') as fecha, 'S' as tipo_moneda, tc.tico_descripcion, CONCAT(nc.nocv_serie,'-',nc.nocv_numero) as numero_documento, c.clie_numero_documento, c.clie_nombre_razon_social, nc.nocv_total_exonerado as total_exonerado, nc.nocv_total_gravado as total_gravado, nc.nocv_total_inafecto as total_inafecto, nc.nocv_subtotal as subtotal, nc.nocv_total_igv as total_igv, nc.nocv_total_icbper as total_icbper, nc.nocv_total as total, nc.nocv_homologacion_estado as homologacion_estado, cs.sede_id, nc.nocv_estado as estado FROM {$shema}.nota_credito_venta nc INNER JOIN {$shema}.cliente c ON c.clie_id = nc.clie_id INNER JOIN {$shema}.comprobante_sede cs ON cs.cose_id = nc.cose_id INNER JOIN {$shema}.tipo_comprobante tc ON tc.tico_id = cs.tico_id WHERE nc.nocv_fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' $sqlsucursal AND nc.nocv_tipo_envio = 'PRODUCCION' ORDER BY cs.sede_id ASC, nc.nocv_fecha ASC, nc.nocv_numero ASC");*/

        $ventas = $query->getResultArray();

        return $this->response->setJSON($ventas);
    }
}
