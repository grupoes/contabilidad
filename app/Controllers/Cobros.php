<?php

namespace App\Controllers;

use App\Models\ContribuyenteModel;
use App\Models\SistemaModel;
use App\Models\MetodoPagoModel;
use App\Models\MovimientoModel;
use App\Models\ServidorModel;
use App\Models\PagoServidorModel;
use App\Models\PagoAnualModel;
use App\Models\PagoAmoAnualModel;
use App\Models\AmortizacionPagoAnualModel;
use App\Models\FechaDeclaracionModel;
use App\Models\AnioModel;
use App\Models\PdtAnualModel;
use App\Models\ServicioModel;
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

    public function renderContribuyentes($servicio, $estado)
    {
        $contribuyente = new ContribuyenteModel();
        $sistema = new SistemaModel();
        $pagoServidor = new PagoServidorModel();

        $cobrar = $this->getPermisosAcciones(13, session()->perfil_id, 'cobrar servidor');

        $sqlServicio = "";

        if ($servicio != 'TODOS') {
            $sqlServicio = "AND c.tipoServicio = '" . $servicio . "'";
        }

        $contribuyentes = $contribuyente->query("SELECT 
            c.id,
            c.ruc,
            c.razon_social,
            COUNT(DISTINCT ps.fecha_inicio) as periodos_deuda,
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
            AND c.estado = $estado
            $sqlServicio
        GROUP BY c.id, c.ruc, c.razon_social
        ORDER BY total_deuda DESC, c.razon_social ASC;")->getResultArray();

        foreach ($contribuyentes as $key => $value) {
            $sistemas = $sistema->query("SELECT s.id, s.nameSystem FROM sistemas s INNER JOIN sistemas_contribuyente sc ON s.id = sc.system_id WHERE sc.contribuyente_id = " . $value['id'])->getResultArray();
            $contribuyentes[$key]['sistemas'] = $sistemas;

            $verificarRegistros = $pagoServidor
                ->select("DATE_FORMAT(fecha_inicio, '%d-%m-%Y') as fecha_inicio, DATE_FORMAT(fecha_fin, '%d-%m-%Y') as fecha_fin")
                ->where('contribuyente_id', $value['id'])
                ->where('estado !=', 'eliminado')
                ->orderBy('id', 'desc')
                ->first();

            if (!$verificarRegistros) {
                $contribuyentes[$key]['pagos'] = "NO TIENE REGISTROS";
                $contribuyentes[$key]['fecha_inicio'] = "";
                $contribuyentes[$key]['fecha_fin'] = "";
            } else {
                $contribuyentes[$key]['fecha_inicio'] = $verificarRegistros['fecha_inicio'];
                $contribuyentes[$key]['fecha_fin'] = $verificarRegistros['fecha_fin'];

                if ($value['periodos_deuda'] == 1) {
                    $contribuyentes[$key]['pagos'] = $value['periodos_deuda'] . " PERIODO";
                } else if ($value['periodos_deuda'] == 0) {
                    $contribuyentes[$key]['pagos'] = "NO DEBE";
                } else {
                    $contribuyentes[$key]['pagos'] = $value['periodos_deuda'] . " PERIODOS";
                }
            }

            $cobrarSer = "";

            if ($cobrar) {
                $cobrarSer = "<a href='" . base_url() . "cobrar-servidor/" . $value['id'] . "' class='btn btn-success'>COBRAR</a>";
            }

            $contribuyentes[$key]['cobrar'] = $cobrarSer;
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

        $servidor->db->transStart();

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

            $servidor->db->transComplete();

            if ($servidor->db->transStatus() === false) {
                throw new \Exception("Error al realizar la operación.");
            }

            return $this->response->setJSON(['status' => 'success', 'message' => "Monto agregado correctamente"]);
        } catch (\Exception $e) {
            $servidor->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function renderPagosServidor($id)
    {
        $pagoServidor = new PagoServidorModel();

        $pagos = $pagoServidor->select("id, DATE_FORMAT(fecha_inicio, '%d-%m-%Y') as fecha_inicio, DATE_FORMAT(fecha_fin, '%d-%m-%Y') as fecha_fin, DATE_FORMAT(fecha_pago, '%d-%m-%Y') as fecha_pago, DATE_FORMAT(fecha_proceso, '%d-%m-%Y') as fecha_proceso, monto_total, monto_pagado, monto_pendiente, usuario_id_cobra, estado")->where('contribuyente_id', $id)->where('estado !=', 'eliminado')->orderBy('id', 'desc')->findAll();

        return $this->response->setJSON($pagos);
    }

    public function renderContribuyentesDeudaAll()
    {

        $data = $this->renderContribuyentesDeuda();
        return $this->response->setJSON($data);
    }

    public function getCobrosAnuales($tipo, $estado)
    {
        $contribuyente = new ContribuyenteModel();

        $cobrar = $this->getPermisosAcciones(13, session()->perfil_id, 'cobrar anual');

        $tipo_servicio = "";

        if ($tipo !== "TODOS") {
            $tipo_servicio = " AND tipoServicio = '$tipo' ";
        }

        $contribuyentes = $contribuyente->query("SELECT c.id, c.ruc, c.razon_social, (SELECT COUNT(*) FROM pago_anual p WHERE p.contribuyente_id = c.id AND p.estado = 'Pendiente') AS pagos_pendientes FROM contribuyentes c INNER JOIN configuracion_notificacion cn ON cn.ruc_empresa_numero = c.ruc where cn.id_tributo IN (11, 12, 13, 14) and c.estado = $estado $tipo_servicio GROUP BY c.id, c.ruc, c.razon_social ORDER BY pagos_pendientes DESC")->getResultArray();

        foreach ($contribuyentes as $key => $value) {
            $cobrarAnual = "";

            if ($cobrar) {
                $cobrarAnual = "<a href='" . base_url() . "cobrar-anual/" . $value['id'] . "' class='btn btn-success'> COBRAR</a>";
            }

            $contribuyentes[$key]['cobrar'] = $cobrarAnual;
        }

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
        $detalle = new AmortizacionPagoAnualModel();

        $pagoAnual->db->transBegin();

        try {

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

            $descripcion = "Pago Anual de " . $dataContrib['razon_social'];

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

            $this->insertPagosAnuales($idPagoAmor, $monto, $idContribuyente, $fecha_proceso);

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

    public function insertPagosAnuales($idPagoAmor, $monto, $idContribuyente, $fecha_proceso)
    {
        $pagoAnual = new PagoAnualModel();
        $detalle = new AmortizacionPagoAnualModel();

        $getPendientes = $pagoAnual->select('SUM(monto_pendiente) as pendientes')->where('estado', 'Pendiente')->where('contribuyente_id', $idContribuyente)->first();

        $pendientes = $getPendientes['pendientes'];

        if ($pendientes == 0) {
            return $this->response->setJSON([
                "status" => "warning",
                "message" => "No hay pagos pendientes para este contribuyente",
            ]);
        }

        if ($monto > $pendientes) {
            return $this->response->setJSON([
                "status" => "warning",
                "message" => "El monto a pagar es mayor al monto pendiente",
            ]);
        }

        $data_pendientes = $pagoAnual->where('estado', 'Pendiente')->where('contribuyente_id', $idContribuyente)->orderBy('id', 'asc')->findAll();

        $i = 0;

        while ($monto > 0) {
            $id = $data_pendientes[$i]['id'];
            $monto_pendiente = $data_pendientes[$i]['monto_pendiente'];
            $monto_pagado = $data_pendientes[$i]['monto_pagado'];

            if ($monto_pendiente <= $monto) {
                $monto_pagado = $monto_pagado + $monto_pendiente;

                $pagoAnual->update($id, [
                    "fecha_pago" => date('Y-m-d H:i:s'),
                    "fecha_proceso" => $fecha_proceso,
                    "monto_pendiente" => 0.00,
                    "monto_pagado" => $monto_pagado,
                    "estado" => "pagado",
                ]);

                $datosPagos = array(
                    "pago_anual_id" => $id,
                    "amop_id" => $idPagoAmor,
                    "monto" => $monto,
                );

                $detalle->insert($datosPagos);

                $monto = $monto - $monto_pendiente;
            } else {
                $monto_pendiente = $monto_pendiente - $monto;

                $monto_pagado = $monto_pagado + $monto;

                $pagoAnual->update($id, [
                    "fecha_pago" => date('Y-m-d H:i:s'),
                    "fecha_proceso" => $fecha_proceso,
                    "monto_pendiente" => $monto_pendiente,
                    "monto_pagado" => $monto_pagado,
                    "estado" => "Pendiente",
                ]);

                $datosPagos = array(
                    "pago_anual_id" => $id,
                    "amop_id" => $idPagoAmor,
                    "monto" => $monto,
                );

                $detalle->insert($datosPagos);

                $monto = 0;
            }

            $i++;
        }
    }

    public function renderAmortizacionAnual($id)
    {
        $paAmor = new PagoAmoAnualModel();
        $detalle = new AmortizacionPagoAnualModel();

        $paAmor->query("SET lc_time_names = 'es_ES'");

        $pagos = $paAmor->query("SELECT p.id, p.contribuyente_id, DATE_FORMAT(p.fecha_pago , '%d-%m-%Y') as fecha_pago, DATE_FORMAT(p.registro, '%d-%m-%Y %H-%i-%s') as registro, p.fecha, p.monto, p.estado, p.vaucher, mp.metodo from amo_pagos_anual p INNER JOIN metodos_pagos mp ON mp.id = p.metodo_pago_id where p.contribuyente_id = $id and p.estado = 1 order by p.id desc")->getResult();

        foreach ($pagos as $key => $value) {
            $id = $value->id;

            $detalle_pagos = $detalle->query("SELECT pa.monto, p.anio_correspondiente FROM amortizacion_pago_anual as pa INNER JOIN pago_anual as p ON p.id = pa.pago_anual_id WHERE pa.amop_id = $id")->getResult();

            $pagos[$key]->pagos = $detalle_pagos;
        }

        return $this->response->setJSON($pagos);
    }

    public function getPagoAnual($id)
    {
        $pago = new PagoAmoAnualModel();

        $data = $pago->find($id);

        return $this->response->setJSON($data);
    }

    public function deletePagoAnual($id)
    {
        $pagoHo = new PagoAmoAnualModel();
        $mov = new MovimientoModel();
        $detalle = new AmortizacionPagoAnualModel();

        $pagoHo->db->transStart();

        try {
            $data = $pagoHo->find($id);

            $contribId = $data['contribuyente_id'];
            $monto = $data['monto'];
            $moviId = $data['movimientoId'];

            $mov->update($moviId, ['mov_estado' => 0]);

            $pagoHo->update($id, ['estado' => 0]);

            $detalle->where('amop_id', $id)->delete();

            $this->deletePagosAnuales($contribId, $monto);

            $pagoHo->db->transComplete();

            if ($pagoHo->db->transStatus() === false) {
                throw new \Exception("Error al realizar la operación.");
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se elimino correctamente"
            ]);
        } catch (\Exception $e) {
            $pagoHo->db->transRollback();

            return $this->response->setJSON([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function deletePagosAnuales($contribId, $monto)
    {
        $pago = new PagoAnualModel();

        $dataPago = $pago->where('contribuyente_id', $contribId)->where('estado !=', 'eliminado')->orderBy('id', 'DESC')->findAll();

        $montoRestante = $monto;

        foreach ($dataPago as $key => $value) {
            if ($montoRestante <= 0) {
                break;
            }

            $montoPagado = $value['monto_pagado'];
            $montoTotal = $value['monto_total'];

            if ($montoRestante >= $montoPagado) {
                $pago->update($value['id'], ['estado' => 'eliminado']);
                $montoRestante -= $montoPagado;
            } else {
                $nuevoMontoPagado = $montoPagado - $montoRestante;
                $nuevoMontoPendiente = $montoTotal - $nuevoMontoPagado;

                $pago->update($value['id'], [
                    'monto_pagado' => $nuevoMontoPagado,
                    'monto_pendiente' => $nuevoMontoPendiente,
                    'estado' => 'pendiente'
                ]);

                $montoRestante = 0;
            }
        }
    }

    public function updatePagoAnual()
    {
        $paAmor = new PagoAmoAnualModel();
        $detalle = new AmortizacionPagoAnualModel();
        $mov = new MovimientoModel();

        try {
            $detalle->db->transBegin();

            $id = $this->request->getVar('id_Pago');
            $monto = $this->request->getVar('monto_mov');
            $datePago = $this->request->getVar('datePago');
            $metodo_pago = $this->request->getVar('metodo_pago');
            $montoActual = $this->request->getVar('montoActual');

            $dataPago = $paAmor->find($id);

            $contribId = $dataPago['contribuyente_id'];

            $montoOriginal = $monto;

            $detalle->where('amop_id', $id)->delete();

            $this->deletePagosAnuales($contribId, $montoActual);

            $this->insertPagosAnuales($id, $monto, $contribId, $datePago);

            $dataPagoHono = [
                "monto" => $montoOriginal,
                "fecha_pago" => $datePago,
                "metodo_pago_id" => $metodo_pago
            ];

            $paAmor->update($id, $dataPagoHono);

            $movId = $dataPago['movimientoId'];

            $dataMov = [
                "mov_monto" => $montoOriginal,
                "mov_fecha_pago" => $datePago,
                "id_metodo_pago" => $metodo_pago
            ];

            $mov->update($movId, $dataMov);

            if ($detalle->db->transStatus() === false) {
                $detalle->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $detalle->db->transCommit();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente"
            ]);
        } catch (\Exception $e) {
            $detalle->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function updateVaucherAnual()
    {
        $pago = new PagoAmoAnualModel();

        try {
            $idPago = $this->request->getVar('idPago');
            $voucher = $this->request->getFile('imagenVoucher');

            $dataPago = $pago->find($idPago);

            $nameFileDelete = $dataPago['vaucher'];

            $filePath = FCPATH . 'pagoAnual/' . $nameFileDelete;

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $nameFile = "";

            if ($voucher->isValid() && !$voucher->hasMoved()) {
                $newName = $voucher->getRandomName();
                $voucher->move(FCPATH . 'pagoAnual', $newName);

                $nameFile = $newName;
            }

            $data = [
                "vaucher" => $nameFile
            ];

            $pago->update($idPago, $data);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function cobroPlanificador()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('cobros/planificador', compact('menu'));
    }

    public function createCobroServicio()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();


        $metodo = new MetodoPagoModel();
        $metodos = $metodo->where('estado', 1)->findAll();

        return view('cobros/newService', compact('menu', 'metodos'));
    }

    public function saveService()
    {
        $service = new ServicioModel();
        try {
            $data = $this->request->getPost();

            $datos = [
                "metodo_id" => $data['metodo_pago'],
                "comprobante_id" => $data['comprobante'],
                "ruc" => $data['numeroDocumento'],
                "razon_social" => $data['razon_social'],
                "monto" => $data['monto'],
                "estado" => $data['estado'],
                "descripcion" => $data['description_service'],
                "url_pdf" => "",
                "url_ticket" => "",
                "user_add" => session()->id,
            ];

            $service->insert($datos);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Se guardo correctamente']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function allServices()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $services = new ServicioModel();

        $data = $services->findAll();

        return $this->response->setJSON($data);
    }

    public function renderDeudoresAnualesAll()
    {
        $data = $this->renderDeudoresAnuales();

        return $this->response->setJSON($data);
    }
}
