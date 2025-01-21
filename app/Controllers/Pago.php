<?php

namespace App\Controllers;

use App\Models\MetodoPagoModel;
use App\Models\TipoComprobanteModel;
use App\Models\ContribuyenteModel;
use App\Models\HistorialTarifaModel;
use App\Models\PagosModel;

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

        $pagos = $pago->query("SELECT p.contribuyente_id, DATE_FORMAT(p.fecha_pago , '%d-%m-%Y') as fecha_pago, DATE_FORMAT(p.mesCorrespondiente, '%d-%m-%Y') as mesCorrespondiente, p.monto_total, p.montoPagado, p.montoPendiente, p.montoExcedente, p.estado, mp.metodo from pagos p inner join metodos_pagos as mp ON mp.id = p.metodo_pago_id where p.contribuyente_id = 1 order by p.id desc")->getResult();

        return $this->response->setJSON($pagos);
    }

    public function pagarHonorario()
    {
        $pago = new PagosModel();
        $contrib = new ContribuyenteModel();

        try {
            $idContribuyente = $this->request->getvar('idcontribuyente');
            $fechaPago = $this->request->getvar('fechaPago');
            $metodoPago = $this->request->getvar('metodoPago');
            $monto = $this->request->getvar('monto');

            $dataContrib = $contrib->where('id', $idContribuyente)->first();

            $getUltimoPago = $pago->where('contribuyente_id', $idContribuyente)->orderBy('id', 'DESC')->first();

            if (!$getUltimoPago) {
                $fechaContratoObj = new DateTime($dataContrib['fechaContrato']);
                $fechaPago = $fechaContratoObj->format('Y-m') . "-".$dataContrib['diaCobro'];

                $diaVence = $fechaPago;
            } else {
                $fecha = new DateTime($getUltimoPago['mesCorrespondiente']); // Fecha inicial
                $fecha->modify('+1 month'); // Sumar un mes
                $diaVence = $fecha->format('Y-m-d');
            }

            $monto_mensual = $this->getMontoMensual($idContribuyente);

            $datos = array(
                "contribuyente_id" => $idContribuyente,
                "metodo_pago_id" => $metodoPago,
                "fecha_pago" => date('Y-m-d H:i:s'),
                "monto_total" => $monto_mensual,
                "mesCorrespondiente" => $diaVence,
                "montoPagado" => $monto,
                "montoPendiente" => 0.00,
                "montoExcedente" => 0.00,
                "usuario_id_cobra" => session()->id,
                "estado" => "pendiente"
            );

            $pago->insert($datos);

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
