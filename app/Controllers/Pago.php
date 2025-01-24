<?php

namespace App\Controllers;

use App\Models\MetodoPagoModel;
use App\Models\TipoComprobanteModel;
use App\Models\ContribuyenteModel;
use App\Models\HistorialTarifaModel;
use App\Models\PagosModel;
use App\Models\PagosHonorariosModel;

use DateTime;

class Pago extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        return view('pagos/index');
    }

    public function pagosHonorarios($id)
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        $contri = new ContribuyenteModel();
        $datos = $contri->find($id);

        $metodo = new MetodoPagoModel();
        $metodos = $metodo->where('estado', 1)->findAll();

        $tarifa = new HistorialTarifaModel();
        
        $monto_mensual = $this->getMontoMensual($id);

        $tipoComprobante = new TipoComprobanteModel();
        $tipos = $tipoComprobante->where('tipo_comprobante_estado', 1)->findAll();

        $fechaActual = new DateTime();

        // Restar 3 días
        $fechaActual->modify('-3 days');

        // Formatear la fecha al formato deseado
        $fechaRestada = $fechaActual->format('Y-m-d');

        return view('pagos/pagar', compact('id', 'metodos', 'tipos', 'datos', 'monto_mensual', 'fechaRestada'));
    }

    public function listaPagos($id)
    {
        $pago = new PagosModel();

        $pagos = $pago->query("SELECT p.contribuyente_id, DATE_FORMAT(p.fecha_pago , '%d-%m-%Y') as fecha_pago, DATE_FORMAT(p.mesCorrespondiente, '%d-%m-%Y') as mesCorrespondiente, p.monto_total, p.montoPagado, p.montoPendiente, p.montoExcedente, p.estado from pagos p where p.contribuyente_id = $id order by p.id desc")->getResult();

        return $this->response->setJSON($pagos);
    }

    public function listaPagosHonorarios($id)
    {
        $pago = new PagosHonorariosModel();

        $pagos = $pago->query("SELECT p.id, p.contribuyente_id, DATE_FORMAT(p.fecha , '%d-%m-%Y') as fecha_pago, DATE_FORMAT(p.registro, '%d-%m-%Y %H-%i-%s') as registro, p.monto, p.estado, p.voucher, mp.metodo from pagos_honorarios p INNER JOIN metodos_pagos mp ON mp.id = p.metodo_pago_id where p.contribuyente_id = $id order by p.id desc")->getResult();

        return $this->response->setJSON($pagos);
    }

    public function pagarHonorario()
    {
        $pago = new PagosModel();
        $contrib = new ContribuyenteModel();
        $paHono = new PagosHonorariosModel();

        try {
            $idContribuyente = $this->request->getvar('idcontribuyente');
            $fechapago = $this->request->getvar('fechaPago');
            $metodoPago = $this->request->getvar('metodoPago');
            $monto = $this->request->getvar('monto');

            $nameFile = "";

            if($metodoPago != 1) {
                $voucher = $this->request->getFile('voucher');

                if ($voucher->isValid() && !$voucher->hasMoved()) {
                    $newName = $voucher->getRandomName();
                    $voucher->move(FCPATH . 'vouchers', $newName);

                    $nameFile = $newName;
                }
            }

            $data_honorario = array(
                "contribuyente_id" => $idContribuyente,
                "registro" => date('Y-m-d H:i:s'),
                "fecha" => $fechapago,
                "metodo_pago_id" => $metodoPago,
                "monto" => $monto,
                "voucher" => $nameFile,
                "estado" => 1
            );

            $paHono->insert($data_honorario);

            $dataContrib = $contrib->where('id', $idContribuyente)->first();

            $getUltimoPago = $pago->where('contribuyente_id', $idContribuyente)->orderBy('id', 'DESC')->first();

            $monto_mensual = $this->getMontoMensual($idContribuyente);

            if (!$getUltimoPago) {
                $fechaContratoObj = new DateTime($dataContrib['fechaContrato']);
                $fechaPago = $fechaContratoObj->format('Y-m') . "-".$dataContrib['diaCobro'];

                $diaVence = $fechaPago;

                $pendiente = 0.00;
                $excedente = 0.00;
                $montoPaga = 0.00;
            } else {

                $pendiente = $getUltimoPago['montoPendiente'];
                $excedente = $getUltimoPago['montoExcedente'];
                $montoPaga = $getUltimoPago['montoPagado'];

                if($getUltimoPago['estado'] === 'Pagado') {
                    $fecha = new DateTime($getUltimoPago['mesCorrespondiente']); // Fecha inicial
                    $fecha->modify('+1 month'); // Sumar un mes
                    $diaVence = $fecha->format('Y-m-d');
                } else {
                    $diaVence = $getUltimoPago['mesCorrespondiente'];
                }

            }

            $montoTotalDisponible = $monto + $excedente - $pendiente;

            if($montoTotalDisponible <= 0) {

                if($montoTotalDisponible == 0) {
                    $mpen = 0;
                    $mpaga = $monto_mensual;
                    $estado = "Pagado";
                } else {
                    $mpaga = $montoPaga + $monto;
                    $mpen = $monto_mensual - $mpaga;
                    $estado = "Pendiente";
                }
                
                $datos = [
                    "contribuyente_id" => $idContribuyente,
                    "monto_total" => $monto_mensual,
                    "mesCorrespondiente" => $diaVence,
                    "montoPagado" => $mpaga,
                    "montoPendiente" => $mpen,
                    "montoExcedente" => 0.00,
                    "usuario_id_cobra" => session()->id,
                    "estado" => $estado
                ];

                $pago->update($getUltimoPago['id'], $datos);
            }

            while ($montoTotalDisponible > 0) {
                $montoMensual = $this->getMontoMensual($idContribuyente);

                if ($montoTotalDisponible <= $montoMensual) {
                    // Pago completo para el mes

                    if($pendiente > 0) {
                        $datos_ac = [
                            "monto_total" => $monto_mensual,
                            "montoPagado" => $montoMensual,
                            "montoPendiente" => 0.00,
                            "montoExcedente" => 0.00,
                            "usuario_id_cobra" => session()->id,
                            "estado" => "Pagado"
                        ];
        
                        $pago->update($getUltimoPago['id'], $datos_ac);

                        $fecha = new DateTime($getUltimoPago['mesCorrespondiente']); // Fecha inicial
                        $fecha->modify('+1 month'); // Sumar un mes
                        $diaCorres = $fecha->format('Y-m-d');
                    } else {
                        $diaCorres = $diaVence;
                    }

                    if($montoTotalDisponible == $montoMensual) {
                        $status = "Pagado";
                    } else {
                        $status = "Pendiente";
                    }

                    $pendientemonto = $montoMensual - $montoTotalDisponible;

                    $datos = [
                        "contribuyente_id" => $idContribuyente,
                        "fecha_pago" => $fechapago,
                        "monto_total" => $montoMensual,
                        "mesCorrespondiente" => $diaCorres,
                        "montoPagado" => $montoTotalDisponible,
                        "montoPendiente" => $pendientemonto,
                        "montoExcedente" => 0.00,
                        "usuario_id_cobra" => session()->id,
                        "estado" => $status
                    ];
    
                    $pago->insert($datos);
                    $montoTotalDisponible -= $montoMensual;
                } else {
                    // Pago parcial o excedente para el último mes

                    if($montoTotalDisponible/$montoMensual >= 2) {
                        $datos = [
                            "contribuyente_id" => $idContribuyente,
                            "fecha_pago" => $fechapago,
                            "monto_total" => $montoMensual,
                            "mesCorrespondiente" => $diaVence,
                            "montoPagado" => $montoTotalDisponible,
                            "montoPendiente" => 0.00,
                            "montoExcedente" => 0.00,
                            "usuario_id_cobra" => session()->id,
                            "estado" => "Pagado"
                        ];
        
                        $pago->insert($datos);

                        $montoTotalDisponible -= $montoMensual;
                    } else {

                        if($montoMensual > $montoTotalDisponible) {
                            $mpendiente = $montoMensual - $montoTotalDisponible;
                            $mexcedente = 0.00;
                            $estado = "Pendiente";
                        } else {
                            $mpendiente = 0.00;
                            $mexcedente = $montoTotalDisponible - $montoMensual;
                            $estado = "Pagado";
                        }

                        $datos = [
                            "contribuyente_id" => $idContribuyente,
                            "fecha_pago" => $fechapago,
                            "monto_total" => $montoMensual,
                            "mesCorrespondiente" => $diaVence,
                            "montoPagado" => $montoTotalDisponible,
                            "montoPendiente" => $mpendiente,
                            "montoExcedente" => $mexcedente,
                            "usuario_id_cobra" => session()->id,
                            "estado" => $estado
                        ];
        
                        $pago->insert($datos);
                        $montoTotalDisponible = 0;

                    }

        
                }

                // Avanzar al siguiente mes
                $fechaMes = new DateTime($diaVence);
                $fechaMes->modify('+1 month');
                $diaVence = $fechaMes->format('Y-m-d');

            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente el pago"
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function renderPagos()
    {
        $contrib = new ContribuyenteModel();

        $data = $contrib->query("SELECT * FROM contribuyentes as c INNER JOIN pagos as p ON p.contribuyente_id = c.id INNER JOIN metodos_pagos as mp ON mp.id = p.metodo_pago_id WHERE p.estado = 'pendiente'")->getResult();

        return $this->response->setJSON($data);
    }
}
