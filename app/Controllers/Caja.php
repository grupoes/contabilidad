<?php

namespace App\Controllers;

use App\Models\SesionCajaModel;
use App\Models\SedeCajaModel;
use App\Models\MovimientoModel;

class Caja extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        $sesionCaja = new SesionCajaModel();

        if(session()->perfil_id == 3){

            $getDataSesionCaja = $sesionCaja->where('id_usuario', session()->id)->orderBy('id_sesion_caja', 'desc')->first();

            $estadoCaja = "";

            if(!$getDataSesionCaja) {
                $estadoCaja = "abrir";
            } else {
                $estadoCaja = $getDataSesionCaja['ses_estado'] == 1 ? 'cerrar': 'abrir';
            }

            return view('caja/cajero', compact('estadoCaja'));
        }

        return view('caja/index');
    }

    public function Aperturar()
    {
        $sesion = new SesionCajaModel();
        $sedeCaja = new SedeCajaModel();

        $sesion->db->transStart();

        try {

            $idUser = session()->id;

            $sesions = $sesion->where('id_usuario', $idUser)->orderBy('id_sesion_caja', 'DESC')->findAll(2);

            if($sesions) {
                $fechaApertura = date('Y-m-d', strtotime($sesions[0]['ses_fechaapertura']));

                if($fechaApertura == date('Y-m-d')) {
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Podrá abrir caja el día de mañana"
                    ]);
                }
            }

            $getSedeCajaFisica = $sedeCaja->where('id_sede', session()->sede_id)->where('id_caja', 1)->first();
            $getSedeCajaVirtual = $sedeCaja->where('id_sede', session()->sede_id)->where('id_caja', 2)->first();

            if (!$getSedeCajaFisica || !$getSedeCajaVirtual) {
                throw new \Exception("No se encontraron las configuraciones de caja.");
            }

            $fecha_apertura = date('Y-m-d H:i:s');

            $datos_fisica = array( 
                "id_usuario" => session()->id,
                "id_sede_caja" => $getSedeCajaFisica['id_sede_caja'],
                "ses_fechaapertura" => $fecha_apertura,
                "ses_montoapertura" => $getSedeCajaFisica['sede_caja_monto'],
                "ses_montocierre" => 0,
                "ses_estado" => 1,
                "ses_fechacierre" => ""
            );

            $sesion->insert($datos_fisica);

            $datos_virtual = array( 
                "id_usuario" => session()->id,
                "id_sede_caja" => $getSedeCajaVirtual['id_sede_caja'],
                "ses_fechaapertura" => $fecha_apertura,
                "ses_montoapertura" => $getSedeCajaVirtual['sede_caja_monto'],
                "ses_montocierre" => 0,
                "ses_estado" => 1,
                "ses_fechacierre" => ""
            );

            $sesion->insert($datos_virtual);

            $sesion->db->transComplete();

            if ($sesion->db->transStatus() === false) {
                throw new \Exception("Error al realizar la operación.");
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "se aperturo caja satisfactoriamente"
            ]);

        } catch (\Throwable $th) {
            $sesion->db->transRollback(); // Revertir la transacción
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrió un error: " . $th->getMessage()
            ]);
        }
        
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

        } catch (\Throwable $th) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrió un error: " . $th->getMessage()
            ]);
        }
    }

    public	function validarcaja()
	{
        $sesion = new SesionCajaModel();

		$html = '1';
        $status = 'success';

        $exist_sesion = $sesion->where('id_usuario', session()->id)->where('ses_estado', 1)->findAll();

        if($exist_sesion) {

            $fecha_apertura = date('Y-m-d', strtotime($exist_sesion[0]['ses_fechaapertura']));

            if($fecha_apertura == date('Y-m-d')) {
                $html = $html;
            } else {
                $html = 'Estimado usuario: Aun no cierra caja del día: 	'.date('d-m-Y', strtotime($exist_sesion[0]['ses_fechaapertura']));
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

        if($sesions) {
            $fisico = "";
            $virtual = "";
            foreach ($sesions as $key => $value) {
                if($value['id_caja'] == 1) {
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

        if($verificar['status'] == 'warning') {
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

        if($verificar['status'] == 'warning') {
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

        if($verificar['status'] == 'warning') {
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

        if($verificar['status'] == 'warning') {
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

}
