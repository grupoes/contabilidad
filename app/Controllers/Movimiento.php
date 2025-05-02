<?php

namespace App\Controllers;

use App\Models\MetodoPagoModel;
use App\Models\TipoComprobanteModel;
use App\Models\MovimientoModel;
use App\Models\SedeModel;
use App\Models\SesionCajaModel;
use DateTime;

class Movimiento extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        if (session()->perfil_id == 3) {

            $metodo = new MetodoPagoModel();
            $tipoComp = new TipoComprobanteModel();

            $metodos = $metodo->where('estado', 1)->findAll();
            $comprobantes = $tipoComp->where('tipo_comprobante_estado', 1)->findAll();

            return view('movimiento/cajero', compact('metodos', 'comprobantes', 'menu'));
        }

        $sede = new SedeModel();

        $sedes = $sede->where('estado', 1)->findAll();

        return view('movimiento/index', compact('sedes', 'menu'));
    }

    public function guardar()
    {
        $mov = new MovimientoModel();

        try {

            $idMovimiento = $this->request->getVar('idMovimiento');
            $tipo_movimiento = $this->request->getVar('tipo_movimiento');
            $conceptoCaja = $this->request->getVar('conceptoCaja');
            $metodoPago = $this->request->getVar('metodoPago');
            $descripcion = $this->request->getVar('descripcion');
            $monto = $this->request->getVar('monto');
            $comprobante = $this->request->getVar('comprobante');

            $idSesionCaja = $this->idSesionCaja($metodoPago);

            $datos = array(
                "id_sesion_caja" => $idSesionCaja,
                "mov_formapago" => 1,
                "id_metodo_pago" => $metodoPago,
                "mov_concepto" => $conceptoCaja,
                "mov_fecha" => date('Y-m-d'),
                "mov_monto" => $monto,
                "mov_estado" => 1,
                "mov_descripcion" => $descripcion,
                "mov_hora" => date('H:i:s'),
                "id_tipo_comprobante" => $comprobante,
                "tipo_comprobante_descripcion" => "",
                "mov_cobro" => ""
            );

            $mov->insert($datos);

            return $this->response->setJSON([
                "status"  => "success",
                "message" => "Se agrego correctamente el movimiento"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrió un error: " . $e->getMessage()
            ]);
        }
    }

    public function showCajero($dateRange)
    {
        if (strpos($dateRange, ' to ') !== false) {
            list($startDate, $endDate) = explode(' to ', $dateRange);

            $startDateFormatted = DateTime::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
            $endDateFormatted = DateTime::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');
        } else {
            $startDateFormatted = DateTime::createFromFormat('d-m-Y', $dateRange)->format('Y-m-d');
            $endDateFormatted = DateTime::createFromFormat('d-m-Y', $dateRange)->format('Y-m-d');
        }


        $mov = new MovimientoModel();

        $movimientos = $mov->query("SELECT m.mov_id,m.mov_monto, m.mov_descripcion, DATE_FORMAT(m.mov_fecha, '%d-%m-%Y') AS fecha, m.mov_fecha, m.id_metodo_pago, mp.metodo, c.caja_descripcion, c2.con_descripcion, m.mov_estado, tm.tipo_movimiento_descripcion FROM movimiento m 
        inner join sesion_caja sc on sc.id_sesion_caja = m.id_sesion_caja
        inner join sede_caja sc2 on sc2.id_sede_caja = sc.id_sede_caja
        inner join caja c on c.id_caja = sc2.id_caja
        inner join metodos_pagos mp on mp.id = m.id_metodo_pago
        inner join concepto c2 on c2.con_id = m.mov_concepto
        inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
        where m.mov_estado = 1 and m.mov_fecha between '$startDateFormatted' and '$endDateFormatted' order by m.mov_id desc")->getResult();

        return $this->response->setJSON($movimientos);
    }

    public function allMetodoPagos()
    {
        $metodo = new MetodoPagoModel();
        $metodos = $metodo->where('estado', 1)->findAll();

        return $this->response->setJSON($metodos);
    }

    public function extornar($id)
    {
        $mov = new MovimientoModel();

        $mov->update($id, ['mov_estado' => 0]);

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Se extornó correctamente el movimiento"
        ]);
    }

    public function cambioPago()
    {
        $mov = new MovimientoModel();

        $idMovimiento = $this->request->getVar('idmov');
        $nuevoMetodoPago = $this->request->getVar('nuevo_metodo_pago');

        $mov->update($idMovimiento, ['id_metodo_pago' => $nuevoMetodoPago]);

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Se cambió correctamente el método de pago"
        ]);
    }

    public function bancosMovimientos()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('movimiento/bancosMovimientos', compact('menu'));
    }

    public function listaVirtualPendientes()
    {
        $mov = new MovimientoModel();

        $datos = $mov->query("SELECT m.mov_id, m.mov_monto, DATE_FORMAT(m.mov_fecha, '%d-%m-%Y') AS fecha, m.mov_fecha, m.id_metodo_pago, mp.metodo, m.mov_estado, m.mov_descripcion, m.nombreUser FROM movimiento m
        inner join metodos_pagos mp on mp.id = m.id_metodo_pago
        where m.mov_estado = 2")->getResult();

        return $this->response->setJSON($datos);
    }

    public function aceptarVirtual($id)
    {
        $mov = new MovimientoModel();

        $sesion = new SesionCajaModel();

        $idUser = session()->id;

        $sesions = $sesion->where('id_usuario', $idUser)->orderBy('id_sesion_caja', 'DESC')->findAll(2);

        $mov->update($id, ['mov_estado' => 1, 'id_sesion_caja' => $sesions[0]['id_sesion_caja']]);

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Se aceptó correctamente el movimiento"
        ]);
    }
}
