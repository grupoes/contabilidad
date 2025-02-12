<?php

namespace App\Controllers;

use App\Models\AnioModel;
use App\Models\MesModel;
use App\Models\PdtAnualModel;
use App\Models\ArchivosPdtAnualModel;
use App\Models\TributoModel;
use App\Models\ContribuyenteModel;

class PdtAnual extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        $anio = new AnioModel();

        $anios = $anio->query("SELECT * FROM anio WHERE anio_estado = 1 AND anio_descripcion <= YEAR(CURDATE()) ORDER BY anio_descripcion DESC")->getResult();

        return view('declaraciones/pdtanual', compact('anios'));
    }

    public function verificar($ruc)
    {
        $tributo = new TributoModel();
        $cont = new ContribuyenteModel();

        $consulta = $tributo->query("SELECT tributo.tri_descripcion,pdt.pdt_descripcion,configuracion_notificacion.ruc_empresa_numero,configuracion_notificacion.id_tributo,tributo.id_pdt
        FROM tributo
        INNER JOIN configuracion_notificacion ON configuracion_notificacion.id_tributo = tributo.id_tributo
        INNER JOIN pdt ON tributo.id_pdt = pdt.id_pdt
        WHERE configuracion_notificacion.ruc_empresa_numero = $ruc and (tributo.id_pdt = 3 or tributo.id_pdt = 4 or tributo.id_pdt = 5 or tributo.id_pdt = 6)")->getResult();

        $data = $cont->where('ruc', $ruc)->first();

        $anual = $data['costoAnual'];

        $datos = array(
            "tipo_pdt" => $consulta,
            "montoAnual" => $anual
        );

        return $this->response->setJSON($datos);
    }


    public function consulta()
    {
        $pdtAnual = new PdtAnualModel();

        $desde = $this->request->getVar("desde");
        $hasta = $this->request->getVar("hasta");
        $ruc = $this->request->getVar('rucNum');

        if($desde > $hasta) {
            $data = [
                "status" => "error",
                "message" => "La fecha de Inicio (desde) no puede ser mayor a la fecha final (hasta)"
            ];

            return $this->response->setJSON($data);
        }

        $consulta = $pdtAnual->query("SELECT * from pdt_anual inner join anio ON anio.id_anio = pdt_anual.periodo inner join archivos_pdtanual ON pdt_anual.id_pdt_anual = archivos_pdtanual.id_pdt_anual where pdt_anual.ruc_empresa = '$ruc' and archivos_pdtanual.estado = 1 and pdt_anual.periodo BETWEEN $desde and $hasta")->getResult();

        $data = [
            "status" => "success",
            "data" => $consulta
        ];

        return $this->response->setJSON($data);
    }

}
