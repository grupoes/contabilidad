<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        return view('home/index');
    }
}
