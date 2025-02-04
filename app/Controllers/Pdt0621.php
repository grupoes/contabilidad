<?php

namespace App\Controllers;

use App\Models\AnioModel;
use App\Models\MesModel;

class Pdt0621 extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        $anio = new AnioModel();
        $mes = new MesModel();

        $anios = $anio->query("SELECT * FROM anio WHERE anio_estado = 1 AND anio_descripcion <= YEAR(CURDATE()) ORDER BY anio_descripcion DESC")->getResult();

        $meses = $mes->where('mes_estado', 1)->findAll();

        return view('declaraciones/pdt0621', compact('anios', 'meses'));
    }

    public function filesSave()
    {
        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            return $this->response->setJSON(['status' => 'success', 'message' => $data]);
        } catch (\Exception $e) {
            
        }
    }

}
