<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        switch (session()->perfil_id) {
            case '3':
                return view('home/cajero');
                break;
            
            default:
                return view('home/index');
                break;
        }

        
    }
}
