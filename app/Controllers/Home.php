<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        switch (session()->perfil_id) {
            case '3':
                return view('home/cajero', compact('menu'));
                break;

            default:
                return view('home/index', compact('menu'));
                break;
        }
    }
}
