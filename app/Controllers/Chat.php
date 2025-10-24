<?php

namespace App\Controllers;

class Chat extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('chat/home', compact('menu'));
    }

    public function chatWhatsapp()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        return view('chat/chat');
    }
}
