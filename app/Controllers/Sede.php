<?php

namespace App\Controllers;

use App\Models\SedeModel;


class Sede extends BaseController
{
    public function index() {}

    public function show()
    {
        $sede = new SedeModel();

        $sedes = $sede->where('estado', 1)->findAll();

        return $this->response->setJSON($sedes);
    }
}
