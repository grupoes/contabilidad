<?php

namespace App\Controllers;

use App\Models\SesionCajaModel;
use App\Models\SedeCajaModel;

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

        $getSedeCaja = $sedeCaja->where('id_sede', session()->sede_id)->where('id_caja', 1)->first();

        $datos = array( 
            "id_usuario" => session()->id,
            "id_sede_caja" => $getSedeCaja['id_sede_caja'],
            "ses_fechaapertura" => date('Y-m-d H:i:s'),
            "ses_montoapertura" => $getSedeCaja['sede_caja_monto'],
            "ses_montocierre" => 0,
            "ses_estado" => 1,
            "ses_fechacierre" => ""
        );

        $sesion->insert($datos);

        return $this->response->setJSON([
            "status" => "success",
            "message" => "se aperturo caja satisfactoriamente"
        ]);
    }

}
