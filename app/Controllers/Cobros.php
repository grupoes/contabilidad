<?php

namespace App\Controllers;

use App\Models\AmorPagosServiciosModel;
use App\Models\ContribuyenteModel;
use App\Models\SistemaModel;
use App\Models\MetodoPagoModel;
use App\Models\MovimientoModel;
use App\Models\ServidorModel;
use App\Models\PagoServidorModel;
use App\Models\PagoAnualModel;
use App\Models\PagoAmoAnualModel;
use App\Models\AmortizacionPagoAnualModel;
use App\Models\ServicioModel;
use App\Models\ServicioPagosModel;

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
        $contribuyentes = $this->renderContribuyentesDeuda($servicio, $estado);

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
        $data = $this->countAllServidorDeuda();
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
        $pagos = new ServicioPagosModel();
        $amorPago = new AmorPagosServiciosModel();

        try {
            $data = $this->request->getPost();
            $estado = $data['estado'];
            $monto = $data['monto'];
            $montos = $data['montos'];

            if ($estado === 'pendiente') {
                $comprobante = 3;

                $total = array_sum($montos);
                if ($total != $monto) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'El monto total de la programación no coincide con el monto del servicio']);
                }

                $hoy = new \DateTime('today');

                $hayAnteriores = false;
                $fechas = $data['fecha_programacion'];

                foreach ($fechas as $fecha) {
                    $f = new \DateTime($fecha);

                    if ($f < $hoy) {
                        $hayAnteriores = true;
                        break;
                    }
                }

                if ($hayAnteriores) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'No se pueden agregar fechas de programación anteriores a la fecha actual si el estado es pendiente']);
                }
            } else {
                $comprobante = $data['comprobante'];
                $metodos = $data['metodo_pago'];
                $total = array_sum($montos);
                if ($total != $monto) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'El monto total de los métodos de pago no coincide con el monto del servicio']);
                }

                if (count($metodos) !== count(array_unique($metodos))) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'No se pueden repetir metodos de pago si el estado es pagado']);
                }
            }

            $datos = [
                "comprobante_id" => $comprobante,
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

            $serviceId = $service->getInsertID();

            if ($estado === 'pendiente') {
                $fechas = $data['fecha_programacion'];

                for ($i = 0; $i < count($montos); $i++) {
                    $datosPago = [
                        "servicio_id" => $serviceId,
                        "monto" => $montos[$i],
                        "fecha_programacion" => $fechas[$i],
                        "monto_pagado" => "0.00",
                        "monto_pendiente" => $montos[$i],
                        "estado" => 'pendiente',
                        "user_add" => session()->id
                    ];

                    $pagos->insert($datosPago);
                }
            } else {
                $metodos = $data['metodo_pago'];

                $datosPago = [
                    "servicio_id" => $serviceId,
                    "monto" => $monto,
                    "fecha_pago" => date('Y-m-d H:i:s'),
                    "fecha_proceso" => date('Y-m-d'),
                    "fecha_programacion" => date('Y-m-d'),
                    "monto_pagado" => $monto,
                    "monto_pendiente" => "0.00",
                    "estado" => 'pagado',
                    "user_add" => session()->id
                ];

                $pagos->insert($datosPago);

                $vaucher = $this->request->getFileMultiple('vaucher');

                for ($i = 0; $i < count($montos); $i++) {

                    $dataSede = $this->Aperturar($metodos[$i], session()->sede_id);

                    if ($metodos[$i] == 1) {
                        $sesionId = $dataSede['idSesionFisica'];
                        $nameFile = "";
                    } else {
                        $sesionId = $dataSede['idSesionVirtual'];

                        if ($vaucher[$i]->isValid() && !$vaucher[$i]->hasMoved()) {
                            $newName = $vaucher[$i]->getRandomName();
                            $vaucher[$i]->move(FCPATH . 'servicios', $newName);

                            $nameFile = $newName;
                        } else {
                            $nameFile = "";
                        }
                    }

                    $idMovimiento = $this->generarMovimiento($sesionId, 1, 1, $metodos[$i], $monto, $data['description_service'], 5, 'TICKET - 0001', 1, date('Y-m-d'), $nameFile, session()->id);

                    $datosAmor = [
                        "servicio_id" => $serviceId,
                        "movimientoId" => $idMovimiento,
                        "registro" => $montos[$i],
                        "fecha_pago" => date('Y-m-d H:i:s'),
                        "metodo_pago_id" => $metodos[$i],
                        "monto" => $montos[$i],
                        "vaucher" => $nameFile,
                        "estado" => 1,
                        "user_add" => session()->id
                    ];

                    $amorPago->insert($datosAmor);
                }
            }

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
