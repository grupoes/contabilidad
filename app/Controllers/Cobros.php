<?php

namespace App\Controllers;

use App\Models\ContribuyenteModel;
use App\Models\SistemaModel;
use App\Models\MetodoPagoModel;
use App\Models\TipoComprobanteModel;

use DateTime;

class Cobros extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('cobros/servidor', compact('menu'));
    }

    public function renderContribuyentes()
    {
        $contribuyente = new ContribuyenteModel();
        $sistema = new SistemaModel();

        $contribuyentes = $contribuyente->query("SELECT DISTINCT c.id, c.ruc, c.razon_social, c.tipoServicio, c.tipoSuscripcion FROM contribuyentes c INNER JOIN sistemas_contribuyente sc ON c.id = sc.contribuyente_id INNER JOIN sistemas s ON sc.system_id = s.id WHERE s.`status` = 1 order by c.id desc;")->getResultArray();

        foreach ($contribuyentes as $key => $value) {
            $sistemas = $sistema->query("SELECT s.id, s.nameSystem FROM sistemas s INNER JOIN sistemas_contribuyente sc ON s.id = sc.system_id WHERE sc.contribuyente_id = " . $value['id'])->getResultArray();
            $contribuyentes[$key]['sistemas'] = $sistemas;
        }

        return $this->response->setJSON($contribuyentes);

    }

    public function cobrarView($id)
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $contri = new ContribuyenteModel();

        $datos = $contri->find($id);

        $metodo = new MetodoPagoModel();
        $metodos = $metodo->where('estado', 1)->findAll();

        //$monto_mensual = $this->getMontoMensual($id);

        $tipoComprobante = new TipoComprobanteModel();
        $tipos = $tipoComprobante->where('tipo_comprobante_estado', 1)->findAll();

        $fechaActual = new DateTime();

        // Restar 3 días
        $fechaActual->modify('-3 days');

        // Formatear la fecha al formato deseado
        $fechaRestada = $fechaActual->format('Y-m-d');


        $countPagos = 0;


        $menu = $this->permisos_menu();

        return view('cobros/cobrarServidor', compact('id', 'metodos', 'tipos', 'datos', 'fechaRestada', 'countPagos', 'menu'));

    }

}
