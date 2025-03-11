<?php

namespace App\Controllers;

use App\Models\DeclaracionModel;

class Declaracion extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $declaracion = new DeclaracionModel();

        $declaraciones = $declaracion->findAll();

        return view('declaracion/index', compact('declaraciones'));
    }
}
