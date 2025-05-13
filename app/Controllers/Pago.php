<?php

namespace App\Controllers;

use App\Models\MetodoPagoModel;
use App\Models\TipoComprobanteModel;
use App\Models\ContribuyenteModel;
use App\Models\HistorialTarifaModel;
use App\Models\PagosModel;
use App\Models\PagosHonorariosModel;
use App\Models\ContratosModel;
use App\Models\MovimientoModel;

use DateTime;

class Pago extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('pagos/index', compact('menu'));
    }

    public function pagosHonorarios($id)
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $contri = new ContribuyenteModel();
        $pagos = new PagosModel();

        $datos = $contri->find($id);

        $metodo = new MetodoPagoModel();
        $metodos = $metodo->where('estado', 1)->findAll();

        $tarifa = new HistorialTarifaModel();

        //$monto_mensual = $this->getMontoMensual($id);

        $tipoComprobante = new TipoComprobanteModel();
        $tipos = $tipoComprobante->where('tipo_comprobante_estado', 1)->findAll();

        $fechaActual = new DateTime();

        // Restar 3 días
        $fechaActual->modify('-3 days');

        // Formatear la fecha al formato deseado
        $fechaRestada = $fechaActual->format('Y-m-d');

        $verificarPago = $pagos->where('contribuyente_id', $id)->findAll();

        $countPagos = "";

        if ($verificarPago) {
            $countPagos = 1;
        } else {
            $countPagos = 0;
        }

        $menu = $this->permisos_menu();

        return view('pagos/pagar', compact('id', 'metodos', 'tipos', 'datos', 'fechaRestada', 'countPagos', 'menu'));
    }

    public function listaPagos($id)
    {
        $pago = new PagosModel();

        $pagos = $pago->query("SELECT p.contribuyente_id, DATE_FORMAT(p.fecha_pago , '%d-%m-%Y') as fecha_pago, DATE_FORMAT(p.fecha_proceso , '%d-%m-%Y') as fecha_proceso, DATE_FORMAT(p.mesCorrespondiente, '%d-%m-%Y') as mesCorrespondiente, p.monto_total, p.montoPagado, p.montoPendiente, p.montoExcedente, p.estado from pagos p where p.contribuyente_id = $id and p.estado != 'eliminado' order by p.id desc")->getResult();

        return $this->response->setJSON($pagos);
    }

    public function listaPagosHonorarios($id)
    {
        $pago = new PagosHonorariosModel();

        $pagos = $pago->query("SELECT p.id, p.contribuyente_id, DATE_FORMAT(p.fecha , '%d-%m-%Y') as fecha_pago, DATE_FORMAT(p.registro, '%d-%m-%Y %H-%i-%s') as registro, p.fecha, p.monto, p.estado, p.voucher, mp.metodo from pagos_honorarios p INNER JOIN metodos_pagos mp ON mp.id = p.metodo_pago_id where p.contribuyente_id = $id and p.estado = 1 order by p.id desc")->getResult();

        return $this->response->setJSON($pagos);
    }

    public function pagarHonorario()
    {
        $pago = new PagosModel();
        $contrib = new ContribuyenteModel();
        $paHono = new PagosHonorariosModel();

        try {
            //$pago->db->transBegin();

            $idContribuyente = $this->request->getvar('idcontribuyente');
            $metodoPago = $this->request->getvar('metodoPago');
            $monto = $this->request->getvar('monto');
            $diaCobro = $this->request->getvar('diaCobro');
            $fecha_proceso = $this->request->getvar('fecha_proceso');

            $nameFile = "";

            if ($metodoPago != 1) {
                $voucher = $this->request->getFile('voucher');

                if ($voucher->isValid() && !$voucher->hasMoved()) {
                    $newName = $voucher->getRandomName();
                    $voucher->move(FCPATH . 'vouchers', $newName);

                    $nameFile = $newName;
                }
            }

            $dataContrib = $contrib->where('id', $idContribuyente)->first();

            $montoMensual = $dataContrib['costoMensual'];

            if (isset($_POST['generarMovimiento'])) {

                $dataSede = $this->Aperturar();

                if ($metodoPago == 1) {
                    $sesionId = $dataSede['idSesionFisica'];
                } else {
                    $sesionId = $dataSede['idSesionVirtual'];
                }

                $descripcion = "Pago de Honorario de " . $dataContrib['razon_social'];

                $idMovimiento = $this->generarMovimiento($sesionId, 1, 1, $metodoPago, $monto, $descripcion, 5, 'TICKET - 0001', 1);

                $data_honorario = array(
                    "contribuyente_id" => $idContribuyente,
                    "movimientoId" => $idMovimiento,
                    "registro" => date('Y-m-d H:i:s'),
                    "fecha" => date('Y-m-d'),
                    "metodo_pago_id" => $metodoPago,
                    "monto" => $monto,
                    "voucher" => $nameFile,
                    "estado" => 1
                );

                $paHono->insert($data_honorario);
            }

            if (isset($_POST['periodo'])) {
                $periodo = $this->request->getvar('periodo') . "-" . $diaCobro;

                $data = array(
                    "contribuyente_id" => $idContribuyente,
                    "fecha_pago" => date('Y-m-d H:i:s'),
                    "fecha_proceso" => $fecha_proceso,
                    "monto_total" => $monto,
                    "mesCorrespondiente" => $periodo,
                    "montoPagado" => $monto,
                    "montoPendiente" => 0,
                    "montoExcedente" => 0,
                    "usuario_id_cobra" => 1,
                    "estado" => "pagado"
                );

                $pago->insert($data);
            } else {
                $lastUtimo = $pago->query("SELECT * FROM pagos WHERE contribuyente_id = $idContribuyente ORDER BY id DESC LIMIT 1")->getRow();

                $montoDisponible = $monto;

                $mesCorrespondiente = $lastUtimo->mesCorrespondiente;

                if ($lastUtimo->estado == "pendiente") {
                    $montoPendiente = $lastUtimo->montoPendiente;

                    $datos = array(
                        "montoPagado" => $montoMensual,
                        "montoPendiente" => 0,
                        "montoExcedente" => 0,
                        "estado" => "pagado"
                    );

                    $pago->update($lastUtimo->id, $datos);

                    $montoDisponible = $montoDisponible - $montoPendiente;
                }

                while ($montoDisponible > 0) {

                    $mesCorrespondiente = date('Y-m', strtotime($mesCorrespondiente . ' + 1 month'));

                    $mesCorrespondiente = $mesCorrespondiente . "-" . $diaCobro;

                    $fecha = new DateTime($mesCorrespondiente);

                    $mesCorrespondiente = $fecha->format('Y-m-d');

                    if ($montoDisponible >= $montoMensual) {
                        $datos = array(
                            "contribuyente_id" => $idContribuyente,
                            "mesCorrespondiente" => $mesCorrespondiente,
                            "monto_total" => $montoMensual,
                            "fecha_pago" => date('Y-m-d H:i:s'),
                            "fecha_proceso" => $fecha_proceso,
                            "montoPagado" => $montoMensual,
                            "montoPendiente" => 0,
                            "montoExcedente" => 0,
                            "estado" => "pagado",
                            "usuario_id_cobra" => session()->id
                        );

                        $pago->insert($datos);

                        $montoDisponible = $montoDisponible - $montoMensual;
                    } else {
                        $datos = array(
                            "contribuyente_id" => $idContribuyente,
                            "mesCorrespondiente" => $mesCorrespondiente,
                            "fecha_pago" => date('Y-m-d H:i:s'),
                            "fecha_proceso" => $fecha_proceso,
                            "monto_total" => $montoMensual,
                            "montoPagado" => $montoDisponible,
                            "montoPendiente" => $montoMensual - $montoDisponible,
                            "montoExcedente" => 0,
                            "estado" => "pendiente",
                            "usuario_id_cobra" => session()->id
                        );

                        $pago->insert($datos);

                        $montoDisponible = 0;
                    }
                }
            }

            /*if ($pago->db->transStatus() === false) {
                $pago->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $pago->db->transCommit();*/

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente el pago"
            ]);
        } catch (\Exception $e) {
            //$pago->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function renderPagos()
    {
        $contrib = new ContribuyenteModel();

        $data = $contrib->query("SELECT * FROM contribuyentes as c INNER JOIN pagos as p ON p.contribuyente_id = c.id INNER JOIN metodos_pagos as mp ON mp.id = p.metodo_pago_id WHERE p.estado = 'pendiente'")->getResult();

        return $this->response->setJSON($data);
    }

    public function insertContratos()
    {
        $contrib = new ContribuyenteModel();
        $historial = new HistorialTarifaModel();

        $contrib->db->transStart();

        try {
            $data = $contrib->where('estado !=', 0)->findAll();

            foreach ($data as $key => $value) {
                $contrato = new ContratosModel();

                $fechaContrato = $value['fechaContrato'];

                $datos = [
                    "contribuyenteId" => $value['id'],
                    "fechaInicio" => $fechaContrato,
                    "fechaFin" => "",
                    "diaCobro" => $value['diaCobro'],
                    "estado" => 1
                ];

                $contrato->insert($datos);

                $id = $contrato->getInsertID();

                $history = $historial->where('contribuyente_id', $value['id'])->orderBy('id', 'ASC')->findAll();

                if ($history) {
                    foreach ($history as $keys => $values) {
                        $dato = [
                            "contratoId" => $id,
                        ];

                        $historial->update($values['id'], $dato);
                    }
                }
            }

            $contrib->db->transComplete();

            if ($contrib->db->transStatus() === false) {
                throw new \Exception("Error al realizar la operación.");
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente"
            ]);
        } catch (\Exception $e) {
            $contrib->db->transRollback();

            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deletePago($id)
    {
        $pago = new PagosModel();
        $pagoHo = new PagosHonorariosModel();
        $mov = new MovimientoModel();

        $data = $pagoHo->find($id);

        $contribId = $data['contribuyente_id'];
        $monto = $data['monto'];
        $moviId = $data['movimientoId'];

        $mov->update($moviId, ['mov_estado' => 0]);

        $pagoHo->update($id, ['estado' => 0]);

        $dataPago = $pago->where('contribuyente_id', $contribId)->where('estado !=', 'eliminado')->orderBy('id', 'DESC')->findAll();

        $i = 0;

        while ($monto > 0) {
            $monto = $monto - $dataPago[$i]['montoPagado'];

            $pago->update($dataPago[$i]['id'], ['estado' => 'eliminado']);

            $i++;
        }

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Se elimino correctamente"
        ]);
    }
}
