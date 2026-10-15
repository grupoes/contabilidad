<?php

namespace App\Controllers;

use App\Models\ContribuyenteModel;
use App\Models\LibroModel;

class Home extends BaseController
{
    /**
     * Libros electrónicos con stock bajo o agotado (misma regla del módulo de libros).
     * Solo para perfiles con permiso "listar" en Libros Electrónicos.
     */
    private function librosEnAlerta()
    {
        $modulo = db_connect()->table('modulos')->select('id')->where('url', 'libros-electronicos')->get()->getRowArray();

        if (!$modulo || !$this->getPermisosAcciones($modulo['id'], session()->perfil_id, 'listar')) {
            return [];
        }

        return array_values(array_filter((new LibroModel())->listar(), fn($l) => $l['estado'] !== 'OK'));
    }

    public function librosAlertas()
    {
        $libros = $this->librosEnAlerta();

        // El enlace al detalle del libro solo se muestra con el permiso "ver detalle"
        if ($libros) {
            $modulo = db_connect()->table('modulos')->select('id')->where('url', 'libros-electronicos')->get()->getRowArray();
            $puedeVer = (bool) $this->getPermisosAcciones($modulo['id'], session()->perfil_id, 'ver detalle');

            foreach ($libros as $k => $l) {
                $libros[$k]['puede_ver'] = $puedeVer;
            }
        }

        return $this->response->setJSON($libros);
    }

    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        $cont = new ContribuyenteModel();
        $contribuyentes = $cont->where('estado', 1)->findAll();
        $countCont = count($contribuyentes);

        $rentas = $this->notificationPdtRentaOptimizado();

        $notificacionSire = $this->countNotificationSire();
        $notificacionAfp = $this->countNotificacionAfp();
        $notificacionPdtRenta = $this->countNotificacionRenta();
        $notificacionPdtPlame = $this->countNotificationPdtPlame();
        $notificacionDeudoresServidor = $this->countAllServidorDeuda();
        $notificacionDeudoresAnuales = count($this->renderDeudoresAnuales());
        $notificacionCertificadosVencer = count($this->certificados_por_vencer());

        $notificacionRenta = $this->notificationPdtRenta();

        $notificacionLibros = count($this->librosEnAlerta());

        switch (session()->perfil_id) {
            case '3':
                return view('home/cajero', compact('menu', 'countCont', 'notificacionSire', 'notificacionAfp', 'notificacionPdtRenta', 'notificacionPdtPlame', 'notificacionDeudoresServidor', 'notificacionDeudoresAnuales', 'notificacionCertificadosVencer', 'notificacionRenta'));
                break;

            case '2':
                return view('home/index', compact('menu', 'countCont', 'notificacionSire', 'notificacionAfp', 'notificacionPdtRenta', 'notificacionPdtPlame', 'notificacionDeudoresServidor', 'notificacionDeudoresAnuales', 'notificacionCertificadosVencer', 'notificacionRenta', 'notificacionLibros'));
                break;
            case '1':
                return view('home/index', compact('menu', 'countCont', 'notificacionSire', 'notificacionAfp', 'notificacionPdtRenta', 'notificacionPdtPlame', 'notificacionDeudoresServidor', 'notificacionDeudoresAnuales', 'notificacionCertificadosVencer', 'notificacionRenta', 'notificacionLibros'));
                break;

            default:
                return view('home/cajero', compact('menu', 'countCont', 'notificacionSire', 'notificacionAfp', 'notificacionPdtRenta', 'notificacionPdtPlame', 'notificacionDeudoresServidor', 'notificacionDeudoresAnuales', 'notificacionCertificadosVencer', 'notificacionRenta'));
                break;
        }
    }

    public function certificadosVencer()
    {
        $vencer = $this->certificados_por_vencer();

        return json_encode($vencer);
    }

    public function reporteAnulacionMasiva()
    {
        $menu = $this->permisos_menu();
        return view('reportes/anulacion_masiva', compact('menu'));
    }
}
