<?php

namespace App\Controllers;

use App\Models\SesionCajaModel;
use App\Models\SedeCajaModel;
use App\Models\MovimientoModel;
use App\Models\SedeModel;
use App\Models\BancosModel;

class Caja extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        $sesionCaja = new SesionCajaModel();

        if (session()->perfil_id == 3) {

            $getDataSesionCaja = $sesionCaja->where('id_usuario', session()->id)->orderBy('id_sesion_caja', 'desc')->first();

            $estadoCaja = "";

            if (!$getDataSesionCaja) {
                $estadoCaja = "abrir";
            } else {
                $estadoCaja = $getDataSesionCaja['ses_estado'] == 1 ? 'cerrar' : 'abrir';
            }


            return view('caja/cajero', compact('estadoCaja', 'menu'));
        }

        $saldos = $this->saldoInicialVirtualBancos();
        $ingresosBancos = $this->ingresoVirtualBancos();
        $egresosBancos = $this->egresoVirtualBancos();

        $ingresosFisicos = $this->ingresosFisicosDiaAll();
        $egresosFisicos = $this->egresosFisicosDiaAll();

        $utilidadFisica = floatval(str_replace(',', '', $ingresosFisicos)) - floatval(str_replace(',', '', $egresosFisicos));

        $utilidadVirtual = floatval(str_replace(',', '', $saldos['total'])) + floatval(str_replace(',', '', $ingresosBancos['total'])) - floatval(str_replace(',', '', $egresosBancos['total']));

        return view('caja/index', compact('menu', 'saldos', 'ingresosBancos', 'egresosBancos', 'utilidadVirtual', 'ingresosFisicos', 'egresosFisicos', 'utilidadFisica'));
    }

    public function cierreCaja()
    {
        $sesion = new SesionCajaModel();

        try {

            $idUser = session()->id;

            $sesions = $sesion->where('id_usuario', $idUser)->orderBy('id_sesion_caja', 'DESC')->findAll(2);

            foreach ($sesions as $key => $value) {
                $data_update = array(
                    "ses_montocierre" => 0,
                    "ses_estado" => 0,
                    "ses_fechacierre" => date('Y-m-d H:i:s')
                );

                $sesion->update($value['id_sesion_caja'], $data_update);
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Caja cerrada correctamente"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrió un error: " . $e->getMessage()
            ]);
        }
    }

    public function validarcaja()
    {
        $sesion = new SesionCajaModel();

        $html = '1';
        $status = 'success';

        $exist_sesion = $sesion->where('id_usuario', session()->id)->where('ses_estado', 1)->findAll();

        if ($exist_sesion) {

            $fecha_apertura = date('Y-m-d', strtotime($exist_sesion[0]['ses_fechaapertura']));

            if ($fecha_apertura == date('Y-m-d')) {
                $html = $html;
            } else {
                $html = 'Estimado usuario: Aun no cierra caja del día: 	' . date('d-m-Y', strtotime($exist_sesion[0]['ses_fechaapertura']));
                $status = 'warning';
            }
        } else {
            $html = 'Estimado usuario: Aun no abrio caja del día';
            $status = 'warning';
        }

        return $this->response->setJSON([
            "status" => $status,
            "message" => $html
        ]);
    }

    public function verificarTipoCaja()
    {
        $sesion = new SesionCajaModel();

        $idUser = session()->id;

        $sesions = $sesion->select('sesion_caja.id_sesion_caja, sesion_caja.ses_estado, sede_caja.id_caja')->join('sede_caja', 'sede_caja.id_sede_caja = sesion_caja.id_sede_caja')->where('sesion_caja.id_usuario', $idUser)->orderBy('sesion_caja.id_sesion_caja', 'DESC')->findAll(2);

        if ($sesions) {
            $fisico = "";
            $virtual = "";
            foreach ($sesions as $key => $value) {
                if ($value['id_caja'] == 1) {
                    $fisico = $value['id_sesion_caja'];
                } else {
                    $virtual = $value['id_sesion_caja'];
                }
            }

            return array("status" => "success", "fisico" => $fisico, "virtual" => $virtual);
        } else {
            return array("status" => "warning");
        }
    }

    public function ingresosFisicos()
    {
        $verificar = $this->verificarTipoCaja();

        if ($verificar['status'] == 'warning') {
            return 0.00;
        } else {
            $fisico = $verificar['fisico'];

            $movimiento = new MovimientoModel();

            $total = $movimiento->select('IFNULL(SUM(mov_monto), 0) as total')->join('concepto', 'concepto.con_id = movimiento.mov_concepto')->where('movimiento.id_sesion_caja', $fisico)->where('concepto.id_tipo_movimiento', 1)->where('movimiento.mov_estado', 1)->first();

            return $total['total'];
        }
    }

    public function egresosFisicos()
    {
        $verificar = $this->verificarTipoCaja();

        if ($verificar['status'] == 'warning') {
            return 0.00;
        } else {
            $fisico = $verificar['fisico'];

            $movimiento = new MovimientoModel();

            $total = $movimiento->select('IFNULL(SUM(mov_monto), 0) as total')->join('concepto', 'concepto.con_id = movimiento.mov_concepto')->where('movimiento.id_sesion_caja', $fisico)->where('concepto.id_tipo_movimiento', 2)->where('movimiento.mov_estado', 1)->first();

            return $total['total'];
        }
    }

    public function ingresosVirtual()
    {
        $verificar = $this->verificarTipoCaja();

        if ($verificar['status'] == 'warning') {
            return 0.00;
        } else {
            $virtual = $verificar['virtual'];

            $movimiento = new MovimientoModel();

            $total = $movimiento->select('IFNULL(SUM(mov_monto), 0) as total')->join('concepto', 'concepto.con_id = movimiento.mov_concepto')->where('movimiento.id_sesion_caja', $virtual)->where('concepto.id_tipo_movimiento', 1)->where('movimiento.mov_estado', 1)->first();

            return $total['total'];
        }
    }

    public function egresosVirtual()
    {
        $verificar = $this->verificarTipoCaja();

        if ($verificar['status'] == 'warning') {
            return 0.00;
        } else {
            $virtual = $verificar['virtual'];

            $movimiento = new MovimientoModel();

            $total = $movimiento->select('IFNULL(SUM(mov_monto), 0) as total')->join('concepto', 'concepto.con_id = movimiento.mov_concepto')->where('movimiento.id_sesion_caja', $virtual)->where('concepto.id_tipo_movimiento', 2)->where('movimiento.mov_estado', 1)->first();

            return $total['total'];
        }
    }

    public function resumenCajaDiaria()
    {
        $ingresosFisicos = $this->ingresosFisicos();
        $egresosFisicos = $this->egresosFisicos();
        $ingresosVirtual = $this->ingresosVirtual();
        $egresosVirtual = $this->egresosVirtual();

        $utilidadFisica = $ingresosFisicos - $egresosFisicos;
        $utilidadVirtual = $ingresosVirtual - $egresosVirtual;

        $utilidad_hoy = $utilidadFisica + $utilidadVirtual;

        return $this->response->setJSON([
            "status" => "success",
            "ingresosFisicos" => $ingresosFisicos,
            "egresosFisicos" => $egresosFisicos,
            "utilidadFisica" => $utilidadFisica,
            "ingresosVirtual" => $ingresosVirtual,
            "egresosVirtual" => $egresosVirtual,
            "utilidadVirtual" => $utilidadVirtual,
            "utilidadHoy" => $utilidad_hoy
        ]);
    }

    public function ingresosFisicosDia($sede)
    {
        $dia = date('Y-m-d');

        $movimiento = new MovimientoModel();

        $total = $movimiento->query("SELECT IFNULL(SUM(movimiento.mov_monto), 0) as total FROM sede_caja INNER JOIN sesion_caja ON sede_caja.id_sede_caja = sesion_caja.id_sede_caja INNER JOIN movimiento ON sesion_caja.id_sesion_caja = movimiento.id_sesion_caja INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id WHERE movimiento.mov_fecha = '$dia' AND id_metodo_pago = 1 AND concepto.id_tipo_movimiento = 1 AND movimiento.mov_estado != 0 AND sede_caja.id_sede = $sede")->getRow();

        return $total->total;
    }

    public function egresosFisicosDia($sede)
    {
        $dia = date('Y-m-d');

        $movimiento = new MovimientoModel();

        $total = $movimiento->query("SELECT IFNULL(SUM(movimiento.mov_monto), 0) as total FROM sede_caja INNER JOIN sesion_caja ON sede_caja.id_sede_caja = sesion_caja.id_sede_caja INNER JOIN movimiento ON sesion_caja.id_sesion_caja = movimiento.id_sesion_caja INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id WHERE movimiento.mov_fecha = '$dia' AND id_metodo_pago = 1 AND concepto.id_tipo_movimiento = 2 AND movimiento.mov_estado = 1 AND sede_caja.id_sede = $sede")->getRow();

        return $total->total;
    }

    public function ingresosVirtualDia($sede)
    {
        $dia = date('Y-m-d');

        $movimiento = new MovimientoModel();

        $total = $movimiento->query("SELECT IFNULL(SUM(movimiento.mov_monto), 0) as total FROM sede_caja LEFT JOIN sesion_caja ON sede_caja.id_sede_caja = sesion_caja.id_sede_caja LEFT JOIN movimiento ON sesion_caja.id_sesion_caja = movimiento.id_sesion_caja LEFT JOIN concepto ON movimiento.mov_concepto = concepto.con_id WHERE movimiento.mov_fecha = '$dia' AND id_metodo_pago != 1 AND concepto.id_tipo_movimiento = 1 AND movimiento.mov_estado != 0 AND sede_caja.id_sede = $sede")->getRow();

        return $total->total;
    }

    public function egresosVirtualDia($sede)
    {
        $dia = date('Y-m-d');

        $movimiento = new MovimientoModel();

        $total = $movimiento->query("SELECT IFNULL(SUM(movimiento.mov_monto), 0) as total FROM sede_caja INNER JOIN sesion_caja ON sede_caja.id_sede_caja = sesion_caja.id_sede_caja INNER JOIN movimiento ON sesion_caja.id_sesion_caja = movimiento.id_sesion_caja INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id WHERE movimiento.mov_fecha = '$dia' AND id_metodo_pago != 1 AND concepto.id_tipo_movimiento = 2 AND movimiento.mov_estado = 1 AND sede_caja.id_sede = $sede")->getRow();

        return $total->total;
    }

    public function resumenCajaDia()
    {
        $sede = new SedeModel();

        $dataSede = $sede->findAll();

        foreach ($dataSede as $key => $value) {
            $idsede = $value['id'];

            $ingresosFisicos = $this->ingresosFisicosDia($idsede);
            $egresosFisicos = $this->egresosFisicosDia($idsede);
            $ingresosVirtual = $this->ingresosVirtualDia($idsede);
            $egresosVirtual = $this->egresosVirtualDia($idsede);

            $utilidadFisica = $ingresosFisicos - $egresosFisicos;
            $utilidadVirtual = $ingresosVirtual - $egresosVirtual;

            $utilidad_hoy = $utilidadFisica + $utilidadVirtual;

            $dataSede[$key]['ingresosFisicos'] = $ingresosFisicos;
            $dataSede[$key]['egresosFisicos'] = $egresosFisicos;
            $dataSede[$key]['utilidadFisica'] = $utilidadFisica;
            $dataSede[$key]['ingresosVirtual'] = $ingresosVirtual;
            $dataSede[$key]['egresosVirtual'] = $egresosVirtual;
            $dataSede[$key]['utilidadVirtual'] = $utilidadVirtual;
            $dataSede[$key]['utilidadHoy'] = $utilidad_hoy;
        }

        return $this->response->setJSON($dataSede);
    }

    public function ingresosFisicosDiaAll()
    {
        $dia = date('Y-m-d');

        $movimiento = new MovimientoModel();

        $total = $movimiento->query("SELECT IFNULL(SUM(movimiento.mov_monto), 0) as total FROM sede_caja INNER JOIN sesion_caja ON sede_caja.id_sede_caja = sesion_caja.id_sede_caja INNER JOIN movimiento ON sesion_caja.id_sesion_caja = movimiento.id_sesion_caja INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id WHERE movimiento.mov_fecha = '$dia' AND id_metodo_pago = 1 AND concepto.id_tipo_movimiento = 1 AND movimiento.mov_estado != 0")->getRow();

        return $total->total;
    }

    public function egresosFisicosDiaAll()
    {
        $dia = date('Y-m-d');

        $movimiento = new MovimientoModel();

        $total = $movimiento->query("SELECT IFNULL(SUM(movimiento.mov_monto), 0) as total FROM sede_caja INNER JOIN sesion_caja ON sede_caja.id_sede_caja = sesion_caja.id_sede_caja INNER JOIN movimiento ON sesion_caja.id_sesion_caja = movimiento.id_sesion_caja INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id WHERE movimiento.mov_fecha = '$dia' AND id_metodo_pago = 1 AND concepto.id_tipo_movimiento = 2 AND movimiento.mov_estado = 1")->getRow();

        return $total->total;
    }

    public function ingresosVirtualDiaAll()
    {
        $dia = date('Y-m-d');

        $movimiento = new MovimientoModel();

        $total = $movimiento->query("SELECT IFNULL(SUM(movimiento.mov_monto), 0) as total FROM sede_caja INNER JOIN sesion_caja ON sede_caja.id_sede_caja = sesion_caja.id_sede_caja INNER JOIN movimiento ON sesion_caja.id_sesion_caja = movimiento.id_sesion_caja INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id WHERE movimiento.mov_fecha = '$dia' AND id_metodo_pago != 1 AND concepto.id_tipo_movimiento = 1 AND movimiento.mov_estado != 0")->getRow();

        return $total->total;
    }

    public function egresosVirtualDiaAll()
    {
        $dia = date('Y-m-d');

        $movimiento = new MovimientoModel();

        $total = $movimiento->query("SELECT IFNULL(SUM(movimiento.mov_monto), 0) as total FROM sede_caja INNER JOIN sesion_caja ON sede_caja.id_sede_caja = sesion_caja.id_sede_caja INNER JOIN movimiento ON sesion_caja.id_sesion_caja = movimiento.id_sesion_caja INNER JOIN concepto ON movimiento.mov_concepto = concepto.con_id WHERE movimiento.mov_fecha = '$dia' AND id_metodo_pago != 1 AND concepto.id_tipo_movimiento = 2 AND movimiento.mov_estado = 1")->getRow();

        return $total->total;
    }

    public function resumenCajaDiaAll()
    {
        $ingresosFisicos = $this->ingresosFisicosDiaAll();
        $egresosFisicos = $this->egresosFisicosDiaAll();
        $ingresosVirtual = $this->ingresosVirtualDiaAll();
        $egresosVirtual = $this->egresosVirtualDiaAll();

        $utilidadFisica = $ingresosFisicos - $egresosFisicos;
        $utilidadVirtual = $ingresosVirtual - $egresosVirtual;

        $utilidad_hoy = $utilidadFisica + $utilidadVirtual;

        return $this->response->setJSON([
            "status" => "success",
            "ingresosFisicos" => $ingresosFisicos,
            "egresosFisicos" => $egresosFisicos,
            "utilidadFisica" => $utilidadFisica,
            "ingresosVirtual" => $ingresosVirtual,
            "egresosVirtual" => $egresosVirtual,
            "utilidadVirtual" => $utilidadVirtual,
            "utilidadHoy" => $utilidad_hoy
        ]);
    }

    public function listaVirtualPendientes()
    {
        $mov = new MovimientoModel();

        $datos = $mov->query("SELECT m.mov_id, m.mov_monto, DATE_FORMAT(m.mov_fecha, '%d-%m-%Y') AS fecha, m.mov_fecha, m.id_metodo_pago, mp.metodo, m.mov_estado, m.mov_descripcion, m.nombreUser FROM movimiento m
        inner join metodos_pagos mp on mp.id = m.id_metodo_pago
        where m.mov_estado = 2")->getResult();

        return $datos;
    }

    public function saldoInicialVirtualBancos()
    {
        $banco = new BancosModel();
        $mov = new MovimientoModel();

        $bancos = $banco->select("id, nombre_banco, saldo_inicial")->where('estado', 1)->findAll();

        $hoy = new \DateTime();
        $hoy->modify('-1 day');
        $fecha = $hoy->format('Y-m-d');

        $suma = 0;

        foreach ($bancos as $key => $value) {
            $id = $value['id'];

            $data = $mov->query("SELECT IFNULL(SUM(m.mov_monto), 0) as total FROM movimiento as m INNER JOIN metodos_pagos as mp ON mp.id = m.id_metodo_pago WHERE mp.id_banco = $id AND m.mov_estado = 1 AND m.mov_fecha <= '$fecha' ")->getRow();
            $saldo = number_format($data->total + $value['saldo_inicial'], 2);
            $bancos[$key]['saldo'] = $saldo;

            $suma = floatval(str_replace(',', '', $suma)) + floatval(str_replace(',', '', $saldo));

            //$suma = $suma + $saldo;
        }

        $datos = [
            "bancos" => $bancos,
            "total" => number_format($suma, 2)
        ];

        return $datos;
    }

    public function ingresoVirtualBancos()
    {
        $banco = new BancosModel();
        $mov = new MovimientoModel();

        $bancos = $banco->select("id, nombre_banco, saldo_inicial")->where('estado', 1)->findAll();

        $fecha = date('Y-m-d');

        $suma = 0;

        foreach ($bancos as $key => $value) {
            $id = $value['id'];

            $datos = $mov->query("SELECT IFNULL(SUM(m.mov_monto), 0) as total FROM movimiento as m INNER JOIN metodos_pagos as mp ON mp.id = m.id_metodo_pago INNER JOIN concepto as c ON c.con_id = m.mov_concepto WHERE mp.id_banco = $id AND m.mov_estado = 1 AND c.id_tipo_movimiento = 1 AND m.mov_fecha = '$fecha' ")->getRow();
            $saldo = number_format($datos->total, 2);
            $bancos[$key]['saldo'] = $saldo;

            $suma = floatval(str_replace(',', '', $suma)) + floatval(str_replace(',', '', $saldo));
        }

        $datos = [
            "bancos" => $bancos,
            "total" => number_format($suma, 2)
        ];

        return $datos;
    }

    public function egresoVirtualBancos()
    {
        $banco = new BancosModel();
        $mov = new MovimientoModel();

        $bancos = $banco->select("id, nombre_banco, saldo_inicial")->where('estado', 1)->findAll();

        $fecha = date('Y-m-d');

        $suma = 0;

        foreach ($bancos as $key => $value) {
            $id = $value['id'];

            $datos = $mov->query("SELECT IFNULL(SUM(m.mov_monto), 0) as total FROM movimiento as m INNER JOIN metodos_pagos as mp ON mp.id = m.id_metodo_pago INNER JOIN concepto as c ON c.con_id = m.mov_concepto WHERE mp.id_banco = $id AND m.mov_estado = 1 AND c.id_tipo_movimiento = 2 AND m.mov_fecha = '$fecha' ")->getRow();
            $saldo = number_format($datos->total, 2);
            $bancos[$key]['saldo'] = $saldo;

            $suma = floatval(str_replace(',', '', $suma)) + floatval(str_replace(',', '', $saldo));
        }

        $datos = [
            "bancos" => $bancos,
            "total" => number_format($suma, 2)
        ];

        return $datos;
    }
}
