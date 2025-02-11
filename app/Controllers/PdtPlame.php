<?php

namespace App\Controllers;

use App\Models\AnioModel;
use App\Models\MesModel;

class PdtPlame extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        $anio = new AnioModel();
        $mes = new MesModel();

        $anios = $anio->query("SELECT * FROM anio WHERE anio_estado = 1 AND anio_descripcion <= YEAR(CURDATE()) ORDER BY anio_descripcion DESC")->getResult();

        $meses = $mes->where('mes_estado', 1)->findAll();

        return view('declaraciones/pdtplame', compact('anios', 'meses'));
    }

    public function filesSave()
    {
        $pdtRenta = new PdtRentaModel();
        $mes = new MesModel();
        $anio_ = new AnioModel();
        $files = new ArchivosPdt0621Model();

        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $file_renta = $this->request->getFile('file_renta');
            $file_constancia = $this->request->getFile('file_constancia');

            if (!$file_renta) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de renta']);
            }

            if (!$file_constancia) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de constancia']);
            }

            if (!$file_renta->isValid() || !$file_constancia->isValid()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
            }

            if ($file_renta->getClientMimeType() !== 'application/pdf' || $file_constancia->getClientMimeType() !== 'application/pdf') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permiten archivos PDF']);
            }

            $ruc = $data['ruc_empresa'];
            $periodo = $data['periodo'];
            $anio = $data['anio'];

            $consultaRenta = $pdtRenta->where('ruc_empresa', $ruc)->where('periodo', $periodo)->where('anio', $anio)->first();

            if($consultaRenta) {
                return $this->response->setJSON(['error' => 'success', 'message' => "El periodo y año ya existe."]);
            }

            $data_periodo = $mes->find($periodo);

            $data_anio = $anio_->find($anio);

            $per = strtoupper($data_periodo['mes_descripcion']);
            $ani = $data_anio['anio_descripcion'];

            $ext_renta = $file_renta->getExtension();
            $ext_constancia = $file_constancia->getExtension();

            $archivo_pdt = "PDT0621_".$ruc."_".$per.$ani.".".$ext_renta;
            $archivo_constancia = "CONST_".$ruc."_".$per.$ani.".".$ext_constancia;

            $file_renta->move(FCPATH . 'archivos/pdt', $archivo_pdt);
            $file_constancia->move(FCPATH . 'archivos/pdt', $archivo_constancia);

            $datos_pdt = array(
                "ruc_empresa" => $ruc,
                "periodo" => $periodo,
                "anio" => $anio,
                "user_id" => session()->id,
                "estado" => 1
            );

            $pdtRenta->insert($datos_pdt);

            $pdtRentaId = $pdtRenta->insertID();

            $datos_files = array(
                "id_pdt_renta" => $pdtRentaId,
                "nombre_pdt" => $archivo_pdt,
                "nombre_constancia" => $archivo_constancia,
                "estado" => 1,
                "user_id" => session()->id
            );

            $files->insert($datos_files);

            return $this->response->setJSON(['status' => 'success', 'message' => "Se registro correctamente"]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

}
