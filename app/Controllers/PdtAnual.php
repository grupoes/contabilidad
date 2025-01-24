<?php

namespace App\Controllers;

use App\Models\SedeModel;

class PdtAnual extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        return view('declaraciones/pdtanual');
    }

}
