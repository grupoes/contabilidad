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

        $notificacionSire = count($this->notificacionSire());
        $notificacionAfp = count($this->notificar_afp());
        $notificacionPdtRenta = count($this->notificationPdtRenta());
        $notificacionPdtPlame = count($this->notificationPdtPlame());
        $notificacionDeudoresServidor = count($this->renderContribuyentesDeuda());
        $notificacionDeudoresAnuales = count($this->renderDeudoresAnuales());
        $notificacionCertificadosVencer = count($this->certificados_por_vencer());

        switch (session()->perfil_id) {
            case '3':
                return view('home/cajero', compact('menu', 'countCont', 'notificacionSire', 'notificacionAfp', 'notificacionPdtRenta', 'notificacionPdtPlame', 'notificacionDeudoresServidor', 'notificacionDeudoresAnuales', 'notificacionCertificadosVencer'));
                break;

            case '2':
                return view('home/index', compact('menu', 'countCont', 'notificacionSire', 'notificacionAfp', 'notificacionPdtRenta', 'notificacionPdtPlame', 'notificacionDeudoresServidor', 'notificacionDeudoresAnuales', 'notificacionCertificadosVencer'));
                break;
            case '1':
                return view('home/index', compact('menu', 'countCont', 'notificacionSire', 'notificacionAfp', 'notificacionPdtRenta', 'notificacionPdtPlame', 'notificacionDeudoresServidor', 'notificacionDeudoresAnuales', 'notificacionCertificadosVencer'));
                break;

            default:
                return view('home/cajero', compact('menu', 'countCont', 'notificacionSire', 'notificacionAfp', 'notificacionPdtRenta', 'notificacionPdtPlame', 'notificacionDeudoresServidor', 'notificacionDeudoresAnuales', 'notificacionCertificadosVencer'));
                break;
        }
    }

    public function certificadosVencer()
    {
        $vencer = $this->certificados_por_vencer();

        return json_encode($vencer);
    }
}
