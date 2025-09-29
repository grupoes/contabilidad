<?php

namespace App\Controllers;

use App\Models\ContribuyenteModel;
use App\Models\SistemaModel;
use App\Models\MetodoPagoModel;
use App\Models\TipoComprobanteModel;
use App\Models\ServidorModel;
use App\Models\PagoServidorModel;

use DateTime;

class Cobros extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('cobros/servidor', compact('menu'));
    }

    public function renderContribuyentes()
    {
        $contribuyente = new ContribuyenteModel();
        $sistema = new SistemaModel();
        $pagoServidor = new PagoServidorModel();

        $contribuyentes = $contribuyente->query("SELECT 
            c.id,
            c.ruc,
            c.razon_social,
            COUNT(DISTINCT ps.fecha_inicio) as periodos_deuda,
            CASE 
                WHEN COUNT(DISTINCT ps.fecha_inicio) = 0 THEN 'Sin deudas'
                ELSE GROUP_CONCAT(DISTINCT ps.fecha_inicio ORDER BY ps.fecha_inicio DESC) 
            END as fechas_vencidas,
            MIN(ps.fecha_inicio) as primera_fecha_vencida,
            DATE_FORMAT(MAX(ps.fecha_inicio), '%d-%m-%Y') as ultima_fecha_vencida,
                DATE_FORMAT(MAX(ps.fecha_fin), '%d-%m-%Y') as ultima_fecha_fin,
            COALESCE((
                SELECT ps2.monto_total 
                FROM pago_servidor ps2 
                WHERE ps2.contribuyente_id = c.id 
                AND ps2.estado = 'pendiente' 
                AND ps2.fecha_inicio < CURDATE()
                ORDER BY ps2.fecha_inicio DESC 
                LIMIT 1
            ), 0) as total_deuda,
            CASE 
                WHEN COUNT(DISTINCT ps.fecha_inicio) = 0 AND EXISTS (
                    SELECT 1 FROM pago_servidor WHERE contribuyente_id = c.id
                ) THEN 'Al día'
                WHEN COUNT(DISTINCT ps.fecha_inicio) = 0 THEN 'Sin registros de pago'
                ELSE 'Con deuda'
            END as estado
        FROM contribuyentes c
        INNER JOIN sistemas_contribuyente sc ON c.id = sc.contribuyente_id
        INNER JOIN sistemas s ON sc.system_id = s.id
        LEFT JOIN pago_servidor ps ON (
            c.id = ps.contribuyente_id 
            AND ps.estado = 'pendiente' 
            AND ps.fecha_inicio < CURDATE()
        )
        WHERE s.status = 1
            AND c.tipoServicio = 'CONTABLE'
            AND c.tipoSuscripcion = 'NO GRATUITO'
        GROUP BY c.id, c.ruc, c.razon_social
        ORDER BY total_deuda DESC, c.razon_social ASC;")->getResultArray();

        foreach ($contribuyentes as $key => $value) {
            $sistemas = $sistema->query("SELECT s.id, s.nameSystem FROM sistemas s INNER JOIN sistemas_contribuyente sc ON s.id = sc.system_id WHERE sc.contribuyente_id = " . $value['id'])->getResultArray();
            $contribuyentes[$key]['sistemas'] = $sistemas;

            $pagos = $pagoServidor->where('contribuyente_id', $value['id'])->where('estado', 'pendiente')->orderBy('id', 'desc')->findAll();

            if (!$pagos) {
                $contribuyentes[$key]['pagos'] = "NO TIENE REGISTROS";
            } else {

                if ($value['periodos_deuda'] == 1) {
                    $contribuyentes[$key]['pagos'] = $value['periodos_deuda'] . " PERIODO";
                } else if ($value['periodos_deuda'] == 0) {
                    $contribuyentes[$key]['pagos'] = "NO DEBE";
                } else {
                    $contribuyentes[$key]['pagos'] = $value['periodos_deuda'] . " PERIODOS";
                }
            }
        }

        return $this->response->setJSON($contribuyentes);
    }

    public function cobrarView($id)
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $contri = new ContribuyenteModel();

        $datos = $contri->find($id);

        $metodo = new MetodoPagoModel();
        $metodos = $metodo->where('estado', 1)->findAll();

        $menu = $this->permisos_menu();

        return view('cobros/cobrarServidor', compact('id', 'datos', 'menu', 'metodos'));
    }

    public function verificarSiTieneCronograma($id)
    {
        $servidor = new ServidorModel();

        $cronograma = $servidor->where('contribuyente_id', $id)->first();

        if (!$cronograma) {
            return $this->response->setJSON(['status' => false]);
        }

        return $this->response->setJSON(['status' => true]);
    }

    public function renderMontos($id)
    {
        $servidor = new ServidorModel();

        $monto = $servidor->select("DATE_FORMAT(fecha_inicio, '%d-%m-%Y') as fecha_inicio, monto")->where('contribuyente_id', $id)->orderBy('id', 'desc')->findAll();

        return $this->response->setJSON($monto);
    }

    public function addMonto()
    {
        $servidor = new ServidorModel();
        $pagoServidor = new PagoServidorModel();

        try {

            $data = $this->request->getPost();

            $id_contribuyente = $data['id_empresa'];
            $monto = $data['addMonto'];
            $fecha = $data['primeraFecha'];

            $fecha_actual = date('Y-m-d');

            $datos_servidor = [
                'contribuyente_id' => $id_contribuyente,
                'monto' => $monto,
                'fecha_inicio' => $fecha,
                'fecha_fin' => '',
                'estado' => 1
            ];

            $servidor->insert($datos_servidor);

            if ($fecha >= $fecha_actual) {
                $fecha_inicio = $fecha;
                $fecha_fin = $this->sumFechaAnioServidor($fecha_inicio);

                $datos_pago_servidor = [
                    'contribuyente_id' => $id_contribuyente,
                    'monto_total' => $monto,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'monto_pagado' => 0,
                    'monto_pendiente' => $monto,
                    'estado' => 'pendiente',
                    'usuario_id_cobra' => session()->id,
                ];

                $pagoServidor->insert($datos_pago_servidor);
            } else {
                $fecha_seguimiento = $fecha;

                while ($fecha < $fecha_actual) {

                    $fecha_inicio = $fecha_seguimiento;
                    $fecha_fin = $this->sumFechaAnioServidor($fecha_inicio);

                    $datos_pago_servidor = [
                        'contribuyente_id' => $id_contribuyente,
                        'monto_total' => $monto,
                        'fecha_inicio' => $fecha_inicio,
                        'fecha_fin' => $fecha_fin,
                        'monto_pagado' => 0,
                        'monto_pendiente' => $monto,
                        'estado' => 'pendiente',
                        'usuario_id_cobra' => session()->id,
                    ];

                    $pagoServidor->insert($datos_pago_servidor);

                    $fecha_seguimiento = $this->sumFechaAnio($fecha_inicio);
                    $fecha = $fecha_fin;
                }
            }

            return $this->response->setJSON(['status' => 'success', 'message' => "Monto agregado correctamente"]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function renderPagosServidor($id)
    {
        $pagoServidor = new PagoServidorModel();

        $pagos = $pagoServidor->select("id, DATE_FORMAT(fecha_inicio, '%d-%m-%Y') as fecha_inicio, DATE_FORMAT(fecha_fin, '%d-%m-%Y') as fecha_fin, DATE_FORMAT(fecha_pago, '%d-%m-%Y') as fecha_pago, DATE_FORMAT(fecha_proceso, '%d-%m-%Y') as fecha_proceso, monto_total, monto_pagado, monto_pendiente, usuario_id_cobra, estado")->where('contribuyente_id', $id)->where('estado !=', 'eliminado')->orderBy('id', 'desc')->findAll();

        return $this->response->setJSON($pagos);
    }

    public function renderContribuyentesDeuda()
    {
        $contribuyente = new ContribuyenteModel();

        $contribuyentes = $contribuyente->query("SELECT 
            c.id,
            c.ruc,
            c.razon_social,
            c.telefono,
            COUNT(ps.id) as periodos_deuda,
            GROUP_CONCAT(DISTINCT ps.fecha_inicio ORDER BY ps.fecha_inicio DESC) as fechas_vencidas,
            MIN(ps.fecha_inicio) as primera_fecha_vencida,
            MAX(ps.fecha_inicio) as ultima_fecha_vencida,
            SUM(ps.monto_pendiente) as total_deuda
        FROM contribuyentes c
        INNER JOIN sistemas_contribuyente sc ON c.id = sc.contribuyente_id
        INNER JOIN sistemas s ON sc.system_id = s.id
        LEFT JOIN pago_servidor ps ON c.id = ps.contribuyente_id 
            AND ps.estado = 'pendiente' 
            AND ps.fecha_inicio < CURDATE()
        WHERE s.status = 1
            AND c.tipoServicio = 'CONTABLE'
            AND c.tipoSuscripcion = 'NO GRATUITO'
        GROUP BY c.id, c.ruc, c.razon_social, c.telefono
        HAVING COUNT(ps.id) > 0
            AND SUM(ps.monto_pendiente) > 0
        ORDER BY SUM(ps.monto_pendiente) DESC;")->getResultArray();

        return $this->response->setJSON($contribuyentes);
    }
}
