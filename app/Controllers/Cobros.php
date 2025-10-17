<?php

namespace App\Controllers;

use App\Models\ContribuyenteModel;
use App\Models\SistemaModel;
use App\Models\MetodoPagoModel;
use App\Models\TipoComprobanteModel;
use App\Models\ServidorModel;
use App\Models\PagoServidorModel;
use App\Models\PagoAnualModel;
use App\Models\PagoAmoAnualModel;
use App\Models\AmortizacionPagoAnualModel;

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

    public function getCobrosAnuales($tipo, $estado)
    {
        $contribuyente = new ContribuyenteModel();

        $tipo_servicio = "";

        if ($tipo !== "TODOS") {
            $tipo_servicio = " AND tipoServicio = '$tipo' ";
        }

        $contribuyentes = $contribuyente->query("SELECT c.id, c.ruc, c.razon_social,  COUNT(pa.id) AS pagos_pendientes FROM contribuyentes c INNER JOIN configuracion_notificacion cn ON cn.ruc_empresa_numero = c.ruc LEFT JOIN pago_anual pa ON pa.contribuyente_id = c.id AND pa.estado = 'Pendiente' where cn.id_tributo != 2 and c.estado = $estado $tipo_servicio GROUP BY c.id, c.ruc, c.razon_social ORDER BY pagos_pendientes DESC")->getResultArray();

        return $this->response->setJSON($contribuyentes);
    }

    public function cobrarAnualView($id)
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $contri = new ContribuyenteModel();

        $datos = $contri->find($id);

        $metodo = new MetodoPagoModel();
        $metodos = $metodo->where('estado', 1)->findAll();

        $menu = $this->permisos_menu();

        return view('cobros/cobrarAnual', compact('id', 'datos', 'menu', 'metodos'));
    }

    public function renderPagosAnual($id)
    {
        $pagoAnual = new PagoAnualModel();

        $pagos = $pagoAnual->select("pago_anual.id, pago_anual.anio_correspondiente, DATE_FORMAT(pago_anual.fecha_pago, '%d-%m-%Y') as fecha_pago, DATE_FORMAT(pago_anual.fecha_proceso, '%d-%m-%Y') as fecha_proceso, pago_anual.monto_total, pago_anual.monto_pagado, pago_anual.monto_pendiente, pago_anual.usuario_id_cobra, pago_anual.estado, pdt_anual.link_pdf")->join('pdt_anual', 'pdt_anual.id_pdt_anual = pago_anual.pdt_anual_id')->where('pago_anual.contribuyente_id', $id)->where('pago_anual.estado !=', 'eliminado')->orderBy('pago_anual.id', 'desc')->findAll();

        return $this->response->setJSON($pagos);
    }

    public function montoAnual($id)
    {
        $pagoAnual = new PagoAnualModel();

        $monto = $pagoAnual->where('estado', 'Pendiente')->where('contribuyente_id', $id)->orderBy('id', 'ASC')->first();

        if ($monto) {
            $datos = array(
                "status" => "success",
                "monto" => $monto['monto_pendiente'],
            );
            return $this->response->setJSON($datos);
        } else {
            $datos = array(
                "status" => "error",
                "monto" => 0,
            );
            return $this->response->setJSON($datos);
        }
    }

    public function pagarAnual()
    {
        $pagoAnual = new PagoAnualModel();
        $contrib = new ContribuyenteModel();
        $paAmor = new PagoAmoAnualModel();

        try {
            $pagoAnual->db->transBegin();

            $idContribuyente = $this->request->getvar('idcontribuyente');
            $metodoPago = $this->request->getvar('metodoPago');
            $monto = $this->request->getvar('monto');
            $fecha_proceso = $this->request->getvar('fecha_proceso');

            $dataContrib = $contrib->where('id', $idContribuyente)->first();

            $nameFile = "";

            if ($metodoPago != 1) {
                $voucher = $this->request->getFile('voucher');

                if ($voucher->isValid() && !$voucher->hasMoved()) {
                    $newName = $voucher->getRandomName();
                    $voucher->move(FCPATH . 'pagoAnual', $newName);

                    $nameFile = $newName;
                }

                $id_sede = "";
            } else {
                $id_sede = $this->request->getvar('selectSede');
            }

            $idPagoAmor = 0;

            $dataSede = $this->Aperturar($metodoPago, $id_sede);

            if ($metodoPago == 1) {
                $sesionId = $dataSede['idSesionFisica'];

                $iduser = $dataSede['idUser'];
            } else {
                $sesionId = $dataSede['idSesionVirtual'];
                +$iduser = session()->id;
            }

            $descripcion = "Pago de Servidor de " . $dataContrib['razon_social'];

            $idMovimiento = $this->generarMovimiento($sesionId, 1, 1, $metodoPago, $monto, $descripcion, 5, 'TICKET - 0001', 1, $fecha_proceso, $nameFile, $iduser);

            $data_honorario = array(
                "contribuyente_id" => $idContribuyente,
                "movimientoId" => $idMovimiento,
                "registro" => date('Y-m-d H:i:s'),
                "fecha" => date('Y-m-d'),
                "fecha_pago" => $fecha_proceso,
                "metodo_pago_id" => $metodoPago,
                "monto" => $monto,
                "vaucher" => $nameFile,
                "estado" => 1
            );

            $paAmor->insert($data_honorario);

            $idPagoAmor = $paAmor->getInsertID();

            $pagos_servidor = $this->procesoPagoAnual($idContribuyente, $monto, $fecha_proceso, $idPagoAmor);

            if ($pagoAnual->db->transStatus() === false) {
                $pagoAnual->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $pagoAnual->db->transCommit();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente el pago"
            ]);
        } catch (\Exception $e) {
            $pagoAnual->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    function procesoPagoAnual($idContribuyente, $monto, $fecha_proceso, $idPagoAmor)
    {
        $pagoAnual = new PagoAnualModel();
        $detallePagos = new AmortizacionPagoAnualModel();
        $servidor = new ServidorModel();

        $consulta_pendientes = $pagoAnual->where('contribuyente_id', $idContribuyente)->where('estado', 'Pendiente')->orderBy('id', 'asc')->findAll();

        foreach ($consulta_pendientes as $key => $value) {

            $monto_total = $value['monto_total'];

            if ($monto == 0) {
                break;
            }

            if ($monto >= $monto_total) {
                $estado = "pagado";
                $newMonto = $monto_total;
                $pendientePago = 0;

                $dataUpdate = array(
                    "fecha_pago" => date('Y-m-d H:i:s'),
                    "fecha_proceso" => $fecha_proceso,
                    "monto_pagado" => $newMonto,
                    "monto_pendiente" => $pendientePago,
                    "usuario_id_cobra" => session()->id,
                    "estado" => $estado,
                );

                $pagoAnual->update($value['id'], $dataUpdate);

                $datosPagos = array(
                    "pago_anual_id" => $value['id'],
                    "amop_id" => $idPagoAmor,
                    "monto" => $newMonto,
                );

                $detallePagos->insert($datosPagos);

                $monto = $monto - $monto_total;
            } else {

                if ($monto >= $value['monto_pendiente']) {
                    $estado = "pagado";
                    $newMonto = $value['monto_total'];
                    $pendientePago = 0;

                    $monto = $monto - $value['monto_pendiente'];
                } else {
                    $estado = "Pendiente";
                    $newMonto = $value['monto_pagado'] + $monto;
                    $pendientePago = $value['monto_pendiente'] - $monto;

                    $monto = 0;
                }

                $dataUpdate = array(
                    "fecha_pago" => date('Y-m-d H:i:s'),
                    "fecha_proceso" => $fecha_proceso,
                    "monto_pagado" => $newMonto,
                    "monto_pendiente" => $pendientePago,
                    "usuario_id_cobra" => session()->id,
                    "estado" => $estado,
                );

                $pagoAnual->update($value['id'], $dataUpdate);

                $datosPagos = array(
                    "pago_anual_id" => $value['id'],
                    "amop_id" => $idPagoAmor,
                    "monto" => $newMonto,
                );

                $detallePagos->insert($datosPagos);
            }
        }

        $monto_servidor = $servidor->where('contribuyente_id', $idContribuyente)->where('estado', 1)->first();
        $monto_server = $monto_servidor['monto'];

        $monto_restante = $monto;

        $ultimo_registro = $pagoAnual->where('contribuyente_id', $idContribuyente)->where('estado !=', 'eliminado')->orderBy('id', 'desc')->first();

        $fecha_inicio = $ultimo_registro['fecha_inicio'];

        while ($monto_restante > 0) {

            if ($ultimo_registro['estado'] == 'Pendiente') {
                $monto_pendiente = $ultimo_registro['monto_pendiente'];

                if ($monto_restante >= $monto_pendiente) {
                    $estado_pago = "pagado";
                    $newMonto_pago = $ultimo_registro['monto_total'];
                    $pendiente_pago = 0;

                    $monto_restante = $monto_restante - $monto_pendiente;
                } else {
                    $estado_pago = "Pendiente";
                    $newMonto_pago = $monto_restante + $monto_pendiente;
                    $pendiente_pago = $monto_pendiente - $monto_restante;

                    $monto_restante = 0;
                }

                $dataUpdate = array(
                    "fecha_pago" => date('Y-m-d H:i:s'),
                    "fecha_proceso" => $fecha_proceso,
                    "monto_pagado" => $newMonto_pago,
                    "monto_pendiente" => $pendiente_pago,
                    "usuario_id_cobra" => session()->id,
                    "estado" => $estado_pago,
                );

                $pagoAnual->update($ultimo_registro['id'], $dataUpdate);
            } else {
                if ($monto_restante >= $monto_server) {
                    $estado_pago = "pagado";
                    $newMonto_pago = $monto_server;
                    $pendiente_pago = 0;

                    $monto_restante = $monto_restante - $monto_server;
                } else {
                    $estado_pago = "Pendiente";
                    $newMonto_pago = $monto_restante;
                    $pendiente_pago = $monto_server - $monto_restante;

                    $monto_restante = 0;
                }

                $fecha_init = $fecha_inicio;

                $newFechaInicio = $this->sumFechaAnio($fecha_init);
                $newFechaFin = $this->sumFechaAnioServidor($newFechaInicio);

                $fecha_inicio = $newFechaInicio;

                $data_pago = array(
                    "contribuyente_id" => $idContribuyente,
                    "fecha_pago" => date('Y-m-d H:i:s'),
                    "fecha_proceso" => $fecha_proceso,
                    "monto_total" => $monto_server,
                    "fecha_inicio" => $fecha_inicio,
                    "fecha_fin" => $newFechaFin,
                    "monto_pendiente" => $pendiente_pago,
                    "monto_pagado" => $newMonto_pago,
                    "usuario_id_cobra" => session()->id,
                    "estado" => $estado_pago,
                );

                $pagoAnual->insert($data_pago);

                $datosPagos = array(
                    "pago_servidor_id" => $pagoAnual->getInsertID(),
                    "pago_amortizacion_id" => $idPagoAmor,
                    "monto" => $newMonto_pago,
                );

                $detallePagos->insert($datosPagos);
            }
        }
    }
}
