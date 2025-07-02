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
                v.vent_fecha AS fecha_real, 
                'S' as tipo_moneda, 
                tc.tico_descripcion, 
                CONCAT(v.vent_serie,'-',v.vent_numero) as numero_documento,
                v.vent_numero as correlativo,
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
            AND DATE(v.vent_fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin' $sqlsucursal
            AND v.vent_tipo_envio = 'PRODUCCION'

            UNION ALL

            SELECT 
                nc.nocv_id as id, 
                TO_CHAR(nc.nocv_fecha, 'DD/MM/YYYY') as fecha, 
                nc.nocv_fecha AS fecha_real,
                'S' as tipo_moneda, 
                tc.tico_descripcion, 
                CONCAT(nc.nocv_serie,'-',nc.nocv_numero) as numero_documento,
                nc.nocv_numero as correlativo,
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
            WHERE DATE(nc.nocv_fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin' $sqlsucursal
            AND nc.nocv_tipo_envio = 'PRODUCCION'
        ) AS subconsulta

        ORDER BY 
            CASE origen 
                WHEN 'venta' THEN 1 
                WHEN 'nota_credito' THEN 2 
            END,
            sede_id ASC,
            fecha_real ASC,
            CAST(correlativo AS INTEGER) ASC;
        ");

        $ventas = $query->getResultArray();

        return $this->response->setJSON($ventas);
    }

    public function maquetaVentas()
    {
        $db = \Config\Database::connect('restaurant');

        $sucursal = $this->request->getPost('sucursal');
        $fecha_inicio = $this->request->getPost('fecha_inicio');
        $fecha_fin = $this->request->getPost('fecha_fin');
        $cuenta = $this->request->getPost('cuenta');
        $glosa = $this->request->getPost('glosa');
        $ruc = $this->request->getPost('ruc');
        $shema = $this->request->getPost('shema');

        $dataSedes = $db->query("SELECT * FROM {$shema}.sede ORDER BY sede_id ASC");

        $sedes = $dataSedes->getResultArray();

        $count = 1;

        if ($sucursal == 0) {
            $count = count($sedes);
        }

        $maqueta = [];

        $tipo_moneda = 'S';
        $tipo_cambio = 1;

        for ($i = 0; $i < $count; $i++) {
            if ($sucursal == 0) {
                $su = $sedes[$i]['sede_id'];
                $sqlsucursal = " AND cs.sede_id = $su ";
            } else {
                $sqlsucursal = " AND cs.sede_id = $sucursal ";
            }

            $dataFacturas = $db->query("SELECT v.vent_id as id, TO_CHAR(v.vent_fecha, 'DD/MM/YYYY') as fecha, 'S' as tipo_moneda, tc.tico_descripcion, CONCAT(v.vent_serie,'-',v.vent_numero) as numero_documento, c.clie_numero_documento, c.clie_nombre_razon_social, v.vent_total_exonerado as total_exonerado, v.vent_total_gravado as total_gravado, v.vent_total_inafecto as total_inafecto, v.vent_subtotal as subtotal, v.vent_total_igv as total_igv, v.vent_total_icbper as total_icbper, v.vent_total as total, v.vent_homologacion_estado as homologacion_estado, cs.sede_id, cs.tico_id, v.vent_estado as estado, '' as referencia, '' as fecha_referencia, 'venta' as origen
            FROM {$shema}.venta v 
            INNER JOIN {$shema}.cliente c ON c.clie_id = v.clie_id 
            INNER JOIN {$shema}.comprobante_sede cs ON cs.cose_id = v.cose_id 
            INNER JOIN {$shema}.tipo_comprobante tc ON tc.tico_id = cs.tico_id 
            WHERE cs.tico_id = 2
            AND DATE(v.vent_fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin' $sqlsucursal AND v.vent_tipo_envio = 'PRODUCCION' ORDER BY v.vent_fecha ASC, v.vent_numero ASC");

            $facturas = $dataFacturas->getResultArray();

            $dataBoletas = $db->query("SELECT v.vent_id as id, TO_CHAR(v.vent_fecha, 'DD/MM/YYYY') as fecha, 'S' as tipo_moneda, tc.tico_descripcion, CONCAT(v.vent_serie,'-',v.vent_numero) as numero_documento, v.vent_serie, v.vent_numero, c.clie_numero_documento, c.clie_nombre_razon_social, v.vent_total_exonerado as total_exonerado, v.vent_total_gravado as total_gravado, v.vent_total_inafecto as total_inafecto, v.vent_subtotal as subtotal, v.vent_total_igv as total_igv, v.vent_total_icbper as total_icbper, v.vent_total as total, v.vent_homologacion_estado as homologacion_estado, cs.sede_id, cs.tico_id, v.vent_estado as estado, '' as referencia, '' as fecha_referencia, 'venta' as origen
            FROM {$shema}.venta v 
            INNER JOIN {$shema}.cliente c ON c.clie_id = v.clie_id 
            INNER JOIN {$shema}.comprobante_sede cs ON cs.cose_id = v.cose_id 
            INNER JOIN {$shema}.tipo_comprobante tc ON tc.tico_id = cs.tico_id 
            WHERE cs.tico_id = 1
            AND DATE(v.vent_fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin' $sqlsucursal AND v.vent_tipo_envio = 'PRODUCCION' ORDER BY v.vent_fecha ASC, v.vent_numero ASC");

            $boletas = $dataBoletas->getResultArray();

            $dataNotasCredito = $db->query("SELECT nc.nocv_id as id, TO_CHAR(nc.nocv_fecha, 'DD/MM/YYYY') as fecha, 'S' as tipo_moneda, tc.tico_descripcion, nc.tico_modifica, CONCAT(nc.nocv_serie,'-',nc.nocv_numero) as numero_documento, c.clie_numero_documento, c.clie_nombre_razon_social, nc.nocv_total_exonerado as total_exonerado, nc.nocv_total_gravado as total_gravado, nc.nocv_total_inafecto as total_inafecto, nc.nocv_subtotal as subtotal, nc.nocv_total_igv as total_igv, nc.nocv_total_icbper as total_icbper, nc.nocv_total as total, nc.nocv_homologacion_estado as homologacion_estado, cs.sede_id, cs.tico_id, nc.nocv_estado as estado, CONCAT(nc.nocv_modifica_serie, '-', nc.nocv_modifica_numero) as referencia, TO_CHAR(v.vent_fecha, 'DD/MM/YYYY') as fecha_referencia, 'nota_credito' as origen, tc.tico_codigo_sunat as codigo_sunat
            FROM {$shema}.nota_credito_venta nc 
            INNER JOIN {$shema}.cliente c ON c.clie_id = nc.clie_id 
            INNER JOIN {$shema}.comprobante_sede cs ON cs.cose_id = nc.cose_id 
            INNER JOIN {$shema}.tipo_comprobante tc ON tc.tico_id = cs.tico_id 
            INNER JOIN {$shema}.venta v ON v.vent_id = nc.vent_id
            WHERE DATE(nc.nocv_fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin' $sqlsucursal
            AND nc.nocv_tipo_envio = 'PRODUCCION' ORDER BY nc.nocv_fecha ASC, nc.nocv_numero ASC");

            $notasCredito = $dataNotasCredito->getResultArray();

            foreach ($facturas as $k => $v) {

                if ($v['estado'] == 'f') {
                    $add = array(
                        "fecha" => $v['fecha'],
                        "tipo_moneda" => $tipo_moneda,
                        "documento" => 'FACTURA',
                        "numero" => $v['numero_documento'],
                        "condicion" => "A",
                        "ruc" => $v['clie_numero_documento'],
                        "razon_social" => $v['clie_nombre_razon_social'],
                        "vventa" => "0.00",
                        "valor_venta" => "0.00",
                        "igv" => "0.00",
                        "bolsa" => "0.00",
                        "icb" => "0.00",
                        "total" => "0.00",
                        "tipo_cambio" => $tipo_cambio,
                        "glosa" => strtoupper($glosa),
                        "cuenta" => $cuenta,
                        "tipo" => "",
                        "referencia" => "",
                        "referenciafecha" => ""
                    );
                } else {
                    $add = array(
                        "fecha" => $v['fecha'],
                        "tipo_moneda" => $tipo_moneda,
                        "documento" => 'FACTURA',
                        "numero" => $v['numero_documento'],
                        "condicion" => "A",
                        "ruc" => $v['clie_numero_documento'],
                        "razon_social" => $v['clie_nombre_razon_social'],
                        "vventa" => $v['subtotal'],
                        "valor_venta" => $v['subtotal'],
                        "igv" => $v['total_igv'],
                        "bolsa" => "0.00",
                        "icb" => $v['total_icbper'],
                        "total" => $v['total'],
                        "tipo_cambio" => $tipo_cambio,
                        "glosa" => strtoupper($glosa),
                        "cuenta" => $cuenta,
                        "tipo" => "",
                        "referencia" => "",
                        "referenciafecha" => ""
                    );
                }

                array_push($maqueta, $add);
            }

            //algoritmo nuevo
            $grupoActual = null;

            foreach ($boletas as $fila) {
                // Si es condición 'I' o monto > 700, agregar directamente
                if ($fila['estado'] === 'f' || $fila['total'] >= 700) {
                    // Si hay un grupo pendiente, agregarlo primero
                    if ($grupoActual !== null) {
                        $maqueta[] =  $this->finalizarGrupo($grupoActual);
                        $grupoActual = null;
                    }

                    $add = $this->agregarFila($fila, $glosa, $cuenta);
                    array_push($maqueta, $add);

                    continue;
                }

                // Si no hay grupo actual, iniciar uno nuevo
                if ($grupoActual === null) {
                    $grupoActual = [
                        'fecha' => $fila['fecha'],
                        'serie' => $fila['vent_serie'],
                        'nums' => [$fila['vent_numero']],
                        'cond' => $fila['estado'],
                        'monto' => $fila['total'],
                        'num_clie' => $fila['clie_numero_documento'],
                        'nombre_clien' => $fila['clie_nombre_razon_social'],
                        'glosa' => $glosa,
                        'cuenta' => $cuenta,
                        'subtotal' => $fila['subtotal'],
                        'total_igv' => $fila['total_igv'],
                        'total_icbper' => $fila['total_icbper']
                    ];

                    continue;
                }

                // Verificar si podemos agregar al grupo actual (misma fecha, serie, cond)
                if (
                    $grupoActual['fecha'] === $fila['fecha'] &&
                    $grupoActual['serie'] === $fila['vent_serie'] &&
                    $grupoActual['cond'] === $fila['estado']
                ) {
                    // Agregar al grupo actual
                    $grupoActual['monto'] += $fila['total'];
                    $grupoActual['subtotal'] += $fila['subtotal'];
                    $grupoActual['total_igv'] += $fila['total_igv'];
                    $grupoActual['total_icbper'] += $fila['total_icbper'];
                    $grupoActual['nums'][] = $fila['vent_numero'];
                } else {
                    // No se puede agregar, cerrar grupo actual y empezar nuevo
                    $maqueta[] =  $this->finalizarGrupo($grupoActual);
                    $grupoActual = [
                        'fecha' => $fila['fecha'],
                        'serie' => $fila['vent_serie'],
                        'nums' => [$fila['vent_numero']],
                        'cond' => $fila['estado'],
                        'monto' => $fila['total'],
                        'num_clie' => $fila['clie_numero_documento'],
                        'nombre_clien' => $fila['clie_nombre_razon_social'],
                        'glosa' => $glosa,
                        'cuenta' => $cuenta,
                        'subtotal' => $fila['subtotal'],
                        'total_igv' => $fila['total_igv'],
                        'total_icbper' => $fila['total_icbper']
                    ];
                }
            }

            // Agregar el último grupo si existe
            if ($grupoActual !== null) {
                $maqueta[] = $this->finalizarGrupo($grupoActual);
            }

            foreach ($notasCredito as $keys => $values) {

                if ($values['tico_modifica'] == 1) {
                    $tipo_referencia = "03";
                } else {
                    $tipo_referencia = "01";
                }

                $add = array(
                    "fecha" => $values['fecha'],
                    "tipo_moneda" => $tipo_moneda,
                    "documento" => 'NOTA DE CREDITO',
                    "numero" => $values['numero_documento'],
                    "condicion" => "A",
                    "ruc" => $values['clie_numero_documento'],
                    "razon_social" => $values['clie_nombre_razon_social'],
                    "vventa" => "-" . number_format($values['subtotal'], 2, '.', ''),
                    "valor_venta" => "-" . number_format($values['subtotal'], 2, '.', ''),
                    "igv" => "-" . number_format($values['total_igv'], 2, '.', ''),
                    "bolsa" => "0.00",
                    "icb" => number_format(0, 2, '.', ''),
                    "total" => "-" . number_format($values['total'], 2, '.', ''),
                    "tipo_cambio" => $tipo_cambio,
                    "glosa" => strtoupper($glosa),
                    "cuenta" => $cuenta,
                    "tipo" => $tipo_referencia,
                    "referencia" => $values['referencia'],
                    "referenciafecha" => $values['fecha_referencia']
                );

                array_push($maqueta, $add);
            }
        }

        return $this->response->setJSON($maqueta);
    }

    public function finalizarGrupo($grupo)
    {
        $result = [
            'fecha' => $grupo['fecha'],
            'tipo_moneda' => "S",
            'documento' => "BOLETA DE VENTA ELECTRONICA",
            'condicion' => "A",
            'ruc' => $grupo['num_clie'],
            'razon_social' => $grupo['nombre_clien'],
            "vventa" => $grupo['subtotal'],
            "valor_venta" => $grupo['subtotal'],
            "igv" => $grupo['total_igv'],
            "bolsa" => "0.00",
            "icb" => $grupo['total_icbper'],
            'total' => $grupo['monto'],
            "tipo_cambio" => "1",
            "glosa" => strtoupper($grupo['glosa']),
            "cuenta" => $grupo['cuenta'],
            "tipo" => "",
            "referencia" => "",
            "referenciafecha" => ""
        ];

        if (count($grupo['nums']) > 1) {
            $result['numero'] = $grupo['serie'] . "-" . $grupo['nums'][0] . '/' . end($grupo['nums']);
        } else {
            $result['numero'] = $grupo['serie'] . "-" . $grupo['nums'][0];
        }

        return $result;
    }

    public function agregarFila($fila, $glosa, $cuenta)
    {
        if ($fila['estado'] == 'f') {
            $add = array(
                "fecha" => $fila['fecha'],
                "tipo_moneda" => "S",
                "documento" => 'BOLETA DE VENTA',
                "numero" => $fila['numero_documento'],
                "condicion" => "A",
                "ruc" => $fila['clie_numero_documento'],
                "razon_social" => $fila['clie_nombre_razon_social'],
                "vventa" => "0.00",
                "valor_venta" => "0.00",
                "igv" => "0.00",
                "bolsa" => "0.00",
                "icb" => "0.00",
                "total" => "0.00",
                "tipo_cambio" => "1",
                "glosa" => strtoupper($glosa),
                "cuenta" => $cuenta,
                "tipo" => "",
                "referencia" => "",
                "referenciafecha" => ""
            );
        } else {
            $add = array(
                "fecha" => $fila['fecha'],
                "tipo_moneda" => "S",
                "documento" => 'BOLETA DE VENTA',
                "numero" => $fila['numero_documento'],
                "condicion" => "A",
                "ruc" => $fila['clie_numero_documento'],
                "razon_social" => $fila['clie_nombre_razon_social'],
                "vventa" => "-" . number_format($fila['subtotal'], 2, '.', ''),
                "valor_venta" => "-" . number_format($fila['subtotal'], 2, '.', ''),
                "igv" => "-" . number_format($fila['total_igv'], 2, '.', ''),
                "bolsa" => "0.00",
                "icb" => number_format(0, 2, '.', ''),
                "total" => "-" . number_format($fila['total'], 2, '.', ''),
                "tipo_cambio" => "1",
                "glosa" => strtoupper($glosa),
                "cuenta" => $cuenta,
                "tipo" => "",
                "referencia" => "",
                "referenciafecha" => ""
            );
        }

        return $add;
    }
}
