<?php

namespace App\Controllers;

use App\Models\SedeModel;
use App\Models\UitModel;

class Configuracion extends BaseController
{
    public function cajaVirtual()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        $sede = new SedeModel();

        $sedes = $sede->where('estado', 1)->findAll();

        return view('configuracion/cajaVirtual', compact('sedes'));
    }

    public function saveCajaVirtual()
    {
        $sede = new SedeModel();

        try {
            $sede_id = $this->request->getVar('sede_id');

            $data_update = array(
                "caja_virtual" => 0
            );

            $sede->set($data_update)->where('1=1')->update();

            $data_new = array(
                "caja_virtual" => 1
            );

            $sede->update($sede_id, $data_new);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se configuro correctamemte"
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function Uit()
    {
        if (!session()->logged_in) {            
            return redirect()->to(base_url());
        }

        $uit = new UitModel();

        $monto_uit = $uit->first();
        
        return view('configuracion/uit', compact('monto_uit'));
    }

    public function saveUit()
    {
        try {
            $uit = new UitModel();

            $id = $this->request->getVar('id');
            $monto = $this->request->getVar('uit');

            $data = array(
                "uit_monto" => $monto
            );

            if ($id) {
                $uit->update($id, $data);
            } else {
                $uit->insert($data);
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente"
            ]);
        } catch (\Exception $e) {
            //throw $th;
        }
    }

    public function renta()
    {
        if (!session()->logged_in) {            
            return redirect()->to(base_url());
        }
        
        return view('configuracion/renta');
    }

    public function contadores()
    {
        if (!session()->logged_in) {            
            return redirect()->to(base_url());
        }
        
        return view('configuracion/contadores');
    }

}
