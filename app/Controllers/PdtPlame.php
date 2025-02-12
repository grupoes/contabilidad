<?php

namespace App\Controllers;

use App\Models\AnioModel;
use App\Models\MesModel;
use App\Models\PdtPlameModel;
use App\Models\ArchivosPdtPlameModel;
use App\Models\R08PlameModel;

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
        $pdtPlame = new PdtPlameModel();
        $files = new ArchivosPdtPlameModel();
        $r08 = new R08PlameModel();

        $pdtPlame->db->transStart();

        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $file_r01 = $this->request->getFile('file_r01');
            $file_r12 = $this->request->getFile('file_r12');
            $file_constancia = $this->request->getFile('file_constancia');

            if (!$file_r01) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo r01']);
            }

            if (!$file_r12) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo r12']);
            }

            if (!$file_constancia) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de constancia']);
            }

            if (!$file_r01->isValid() || !$file_r12->isValid() || !$file_constancia->isValid()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
            }

            if ($file_r01->getClientMimeType() !== 'application/pdf' && $file_r01->getClientMimeType() !== 'application/vnd.ms-excel' && $file_r01->getClientMimeType() !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permiten archivos PDF o Excel en R01']);
            }

            if ($file_r12->getClientMimeType() !== 'application/pdf' && $file_r12->getClientMimeType() !== 'application/vnd.ms-excel' && $file_r12->getClientMimeType() !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permiten archivos PDF o Excel en R12']);
            }

            if ($file_constancia->getClientMimeType() !== 'application/pdf' && $file_constancia->getClientMimeType() !== 'application/msword' && $file_constancia->getClientMimeType() !== 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permiten archivos PDF o Excel en Constancia']);
            }

            $ruc = $data['ruc_empresa'];
            $periodo = $data['periodo'];
            $anio = $data['anio'];

            $consultaPlame = $pdtPlame->where('ruc_empresa', $ruc)->where('periodo', $periodo)->where('anio', $anio)->first();

            if($consultaPlame) {
                return $this->response->setJSON(['error' => 'success', 'message' => "El periodo y año ya existe."]);
            }

            $name_r01 = $file_r01->getName();
            $name_r12 = $file_r12->getName();
            $name_constancia = $file_constancia->getName();

            $file_r01->move(FCPATH . 'archivos/pdt', $name_r01);
            $file_r12->move(FCPATH . 'archivos/pdt', $name_r12);
            $file_constancia->move(FCPATH . 'archivos/pdt', $name_constancia);

            $datos_pdt = array(
                "ruc_empresa" => $ruc,
                "periodo" => $periodo,
                "anio" => $anio,
                "user_id" => session()->id,
                "estado" => 1
            );

            $pdtPlame->insert($datos_pdt);

            $pdtPlameId = $pdtPlame->insertID();

            $datos_files = array(
                "id_pdtplame" => $pdtPlameId,
                "archivo_planilla" => $name_r01,
                "archivo_honorarios" => $name_r12,
                "archivo_constancia" => $name_constancia,
                "estado" => 1,
                "user_id" => session()->id
            );

            $files->insert($datos_files);

            $file_r08 = $this->request->getFileMultiple('file_r08');

            for ($i=0; $i < count($file_r08); $i++) { 
                $name = $file_r08[$i]->getName();

                $file_r08[$i]->move(FCPATH . 'archivos/pdt', $name);

                $data_r08 = array(
                    "plameId" => $pdtPlameId,
                    "nameFile" => $name,
                    "status" => 1,
                    "user_id" => session()->id
                );

                $r08->insert($data_r08);
            }

            $pdtPlame->db->transComplete();

            if ($pdtPlame->db->transStatus() === false) {
                throw new \Exception("Error al realizar la operación.");
            }

            return $this->response->setJSON(['status' => 'success', 'message' => "Se guardo correctamente"]);

        } catch (\Exception $e) {
            log_message('error', 'Error en la transacción: ' . $e->getMessage());
            $pdtPlame->db->transRollback();

            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function consulta()
    {
        $pdtPlame = new PdtPlameModel();

        $periodo = $this->request->getVar('periodo');
        $anio = $this->request->getVar('anio');
        $ruc = $this->request->getVar('ruc');

        $consulta = $pdtPlame->query("SELECT
        pdt_plame.periodo,pdt_plame.anio,archivos_pdtplame.id_archivos_pdtplame,archivos_pdtplame.archivo_planilla,archivos_pdtplame.archivo_honorarios,archivos_pdtplame.archivo_constancia,archivos_pdtplame.estado,archivos_pdtplame.id_pdtplame,anio.anio_descripcion,mes.mes_descripcion
        FROM pdt_plame
        INNER JOIN archivos_pdtplame ON archivos_pdtplame.id_pdtplame = pdt_plame.id_pdt_plame
        INNER JOIN anio ON pdt_plame.anio = anio.id_anio
        INNER JOIN mes ON mes.id_mes = pdt_plame.periodo
        WHERE pdt_plame.ruc_empresa = $ruc AND pdt_plame.anio = $anio AND pdt_plame.periodo = $periodo AND archivos_pdtplame.estado = 1")->getResult();

        return $this->response->setJSON($consulta);
    }

}
