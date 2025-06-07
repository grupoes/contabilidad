<?php

namespace App\Controllers;

use App\Models\AnioModel;
use App\Models\MesModel;
use App\Models\PdtAnualModel;
use App\Models\ArchivosPdtAnualModel;
use App\Models\TributoModel;
use App\Models\ContribuyenteModel;
use App\Models\PdtModel;

class PdtAnual extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $anio = new AnioModel();

        $anios = $anio->query("SELECT * FROM anio WHERE anio_estado = 1 AND anio_descripcion <= YEAR(CURDATE()) ORDER BY anio_descripcion DESC")->getResult();

        $menu = $this->permisos_menu();

        return view('declaraciones/pdtanual', compact('anios', 'menu'));
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

        if ($desde > $hasta) {
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

    public function getBalance()
    {
        $pdtAnual = new PdtAnualModel();

        $anio = $this->request->getVar('anio');
        $ruc = $this->request->getVar('ruc');
        $tipoPdt = $this->request->getVar('pdt');

        $sql = "AND pdt_anual.periodo = $anio";

        if ($tipoPdt != "0") {
            $sql = "AND pdt_anual.periodo = $anio AND pdt_anual.id_pdt_tipo = $tipoPdt";
        }

        $consulta = $pdtAnual->query("SELECT pdt.pdt_descripcion,pdt_anual.ruc_empresa,pdt_anual.periodo,pdt_anual.id_pdt_tipo,archivos_pdtanual.id_pdt_anual,archivos_pdtanual.id_archivo_anual,archivos_pdtanual.pdt,archivos_pdtanual.constancia,anio.anio_descripcion
        FROM pdt_anual
        INNER JOIN pdt ON pdt_anual.id_pdt_tipo = pdt.id_pdt
        INNER JOIN archivos_pdtanual ON pdt_anual.id_pdt_anual = archivos_pdtanual.id_pdt_anual
        INNER JOIN anio ON pdt_anual.periodo = anio.id_anio
        WHERE pdt_anual.ruc_empresa = $ruc AND archivos_pdtanual.estado = 1 $sql")->getResult();

        return $this->response->setJSON($consulta);
    }

    public function guardar()
    {
        $pdtAnual = new PdtAnualModel();
        $archivosPdtAnual = new ArchivosPdtAnualModel();
        $pdt = new PdtModel();
        $anio = new AnioModel();

        $anio_post = $this->request->getVar('anio');
        $typePdt = $this->request->getVar('typePdt');

        $cargo = $this->request->getVar('cargo');
        $monto = $this->request->getVar('monto');
        $descripcion = $this->request->getVar('descripcion');
        $ruc = $this->request->getVar('idruc');

        $consulta = $pdtAnual->where("ruc_empresa", $ruc)->where("id_pdt_tipo", $typePdt)->where("periodo", $anio)->first();

        if ($consulta) {
            $data = [
                "status" => "error",
                "message" => "Ya existe un registro para el año y tipo de PDT seleccionado"
            ];

            return $this->response->setJSON($data);
        }

        $isCargo = 0;

        if (isset($cargo)) {
            $isCargo = 1;
        }

        $data_pdt_anual = [
            "ruc_empresa" => $ruc,
            "periodo" => $anio,
            "id_pdt_tipo" => $typePdt,
            "cargo" => $isCargo,
            "user_add" => session()->id_usuario,
            "estado" => 1
        ];

        $pdtAnual->insert($data_pdt_anual);

        $id = $pdtAnual->getInsertID();

        $pdt_file = $this->request->getFile('pdt');
        $constancia = $this->request->getFile('constancia');

        if (!$pdt_file) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de pdt']);
        }

        if (!$constancia) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de constancia']);
        }

        if (!$pdt_file->isValid() || !$constancia->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
        }

        if ($pdt_file->getClientMimeType() !== 'application/pdf' || $constancia->getClientMimeType() !== 'application/pdf') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permiten archivos PDF']);
        }

        $dataPdt = $pdt->select("pdt_descripcion")->find($typePdt);
        $nombre_pdt = $dataPdt['pdt_descripcion'];

        $dataAnio = $anio->select("anio_descripcion")->find($anio_post);
        $anio_descripcion = $dataAnio['anio_descripcion'];

        $data_archivos_pdt_anual = [
            "id_pdt_anual" => $id,
            "pdt" => $pdt,
            "constancia" => $constancia,
            "monto" => $monto,
            "descripcion" => $descripcion,
            "user_add" => session()->id_usuario,
            "estado" => 1
        ];

        $archivosPdtAnual->insert($data_archivos_pdt_anual);

        $data = [
            "status" => "success",
            "message" => "Registro guardado correctamente"
        ];

        return $this->response->setJSON($data);
    }
}
