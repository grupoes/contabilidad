<?php

namespace App\Controllers;

use App\Models\MetodoPagoModel;
use App\Models\TipoComprobanteModel;
use App\Models\ContribuyenteModel;
use App\Models\HistorialTarifaModel;

class Pago extends BaseController
{
    public function pagosHonorarios($id)
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        $contri = new ContribuyenteModel();
        $datos = $contri->find($id);

        $metodo = new MetodoPagoModel();
        $metodos = $metodo->where('estado', 1)->findAll();

        $tarifa = new HistorialTarifaModel();
        $idTarifa = $tarifa->query("SELECT t.id,
            t.monto_mensual,
            t.fecha_inicio
        FROM historial_tarifas t
        WHERE t.fecha_inicio <= CURRENT_DATE and t.contribuyente_id = $id
        ORDER BY t.fecha_inicio DESC
        LIMIT 1;")->getRow();

        $tipoComprobante = new TipoComprobanteModel();
        $tipos = $tipoComprobante->where('tipo_comprobante_estado', 1)->findAll();

        return view('pagos/pagar', compact('id', 'metodos', 'tipos', 'datos', 'idTarifa'));
    }
}
