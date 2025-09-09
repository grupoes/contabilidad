<?php

namespace App\Controllers;

use App\Models\HonorariosModel;
use App\Models\FacturasHonorariosModel;

class Facturas extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('facturas/index', compact('menu'));
    }

    public function listarFacturasPeriodo()
    {
        $honorarios = new HonorariosModel();

        $facturas = $honorarios->query("SELECT h.id, h.descripcion, COUNT(fh.honorario_id) as total_facturas FROM honorarios h LEFT JOIN facturas_honorarios fh ON fh.honorario_id = h.id WHERE h.estado = 1 GROUP BY h.id, h.descripcion ORDER BY h.id DESC ")->getResult();

        return $this->response->setJSON($facturas);
    }

    public function facturasLista($id)
    {
        $facturas = new FacturasHonorariosModel();

        $facturas = $facturas->query("SELECT c.ruc, c.razon_social, c.tipoServicio, c.tipoPago, fh.serie_comprobante, fh.numero_comprobante, fh.url_absoluta_a4, fh.url_absoluta_ticket, fh.monto FROM facturas_honorarios fh INNER JOIN contribuyentes c ON c.id = fh.contribuyente_id WHERE fh.honorario_id = $id")->getResult();

        return $this->response->setJSON($facturas);
    }
}
