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

        $sesion->db->transStart();

        try {

            $getSedeCajaFisica = $sedeCaja->where('id_sede', session()->sede_id)->where('id_caja', 1)->first();
            $getSedeCajaVirtual = $sedeCaja->where('id_sede', session()->sede_id)->where('id_caja', 2)->first();

            if (!$getSedeCajaFisica || !$getSedeCajaVirtual) {
                throw new \Exception("No se encontraron las configuraciones de caja.");
            }

            $datos_fisica = array( 
                "id_usuario" => session()->id,
                "id_sede_caja" => $getSedeCajaFisica['id_sede_caja'],
                "ses_fechaapertura" => date('Y-m-d H:i:s'),
                "ses_montoapertura" => $getSedeCajaFisica['sede_caja_monto'],
                "ses_montocierre" => 0,
                "ses_estado" => 1,
                "ses_fechacierre" => ""
            );

            $sesion->insert($datos_fisica);

            $datos_virtual = array( 
                "id_usuario" => session()->id,
                "id_sede_caja" => $getSedeCajaVirtual['id_sede_caja'],
                "ses_fechaapertura" => date('Y-m-d H:i:s'),
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

}
