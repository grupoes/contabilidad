<?php

namespace App\Controllers;

class Movimiento extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        if(session()->perfil_id == 3){
            return view('movimiento/cajero');
        }

        return view('movimiento/index');
    }
}
