<?php

namespace App\Controllers;

use App\Models\ConceptoModel;

class Concepto extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        return view('concepto/index');
    }

    public function renderConceptos()
    {
        $concepto = new ConceptoModel();

        $conceptos = $concepto->join('tipo_movimiento','tipo_movimiento.id_tipo_movimiento = concepto.id_tipo_movimiento')->where('concepto.con_estado', 1)->findAll();

        return $this->response->setJSON($conceptos);
    }

    public function conceptosTipoMovimiento($tipo)
    {
        $concepto = new ConceptoModel();

        $conceptos = $concepto->where('con_estado', 1)->where('id_tipo_movimiento', $tipo)->findAll();

        return $this->response->setJSON($conceptos);
    }
}
