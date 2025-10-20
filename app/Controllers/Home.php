<?php

namespace App\Controllers;

use App\Models\ContribuyenteModel;

class Home extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        $cont = new ContribuyenteModel();
        $contribuyentes = $cont->where('estado', 1)->findAll();
        $countCont = count($contribuyentes);

        switch (session()->perfil_id) {
            case '3':
                return view('home/cajero', compact('menu', 'countCont'));
                break;

            case '2':
                return view('home/index', compact('menu', 'countCont'));
                break;
            case '1':
                return view('home/index', compact('menu', 'countCont'));
                break;

            default:
                return view('home/cajero', compact('menu', 'countCont'));
                break;
        }
    }
}
