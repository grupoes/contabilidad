<?php

namespace App\Controllers;

use App\Models\AnioModel;
use App\Models\MesModel;

class PdtAnual extends BaseController
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

        return view('declaraciones/pdtanual', compact('anios', 'meses'));
    }

}
