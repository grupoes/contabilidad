<?php

namespace App\Controllers;

class Configuracion extends BaseController
{
    public function cajaVirtual()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        return view('configuracion/cajaVirtual');
    }

}
