<?php

namespace App\Controllers;

use App\Models\AnioModel;
use App\Models\MesModel;
use App\Models\BoletaPagoModel;
use App\Models\ArchivosBoletaPagoModel;

class BoletaPago extends BaseController
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

        return view('declaraciones/boletaPago', compact('anios', 'meses'));
    }

    public function save()
    {
        $boletaPago = new BoletaPagoModel();
        $anio_ = new AnioModel();
        $mes_ = new MesModel();
        $files = new ArchivosBoletaPagoModel();

        $boletaPago->db->transStart();

        try {
        
            $ruc = $this->request->getVar('rucEmp');
            $anio = $this->request->getVar('anio');
            $mes = $this->request->getVar('periodo');

            $consulta_boleta = $boletaPago->query("SELECT * FROM boleta_pago WHERE ruc_empresa = $ruc AND periodo = $mes AND anio = $anio")->getResult();

            if($consulta_boleta) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Ya existe el Periodo y el Año"
                ]);
            }

            $micarpeta = FCPATH.'archivos/boletas_pago/' . $ruc;
            if (!file_exists($micarpeta)) {
                mkdir($micarpeta, 0777, true);
            }

            $data_anio = $anio_->find($anio);

            $data_periodo = $mes_->find($mes);

            $periodo = $data_periodo['mes_fecha'];

            $ani = $data_anio['anio_descripcion'];

            $periodo_anio = $micarpeta . '/' . $periodo . $ani;
            if (!file_exists($periodo_anio)) {
                mkdir($periodo_anio, 0777, true);
            }

            $data_boleta = array(
                "ruc_empresa" => $ruc,
                "periodo" => $mes,
                "anio" => $anio,
                "estado" => 1,
                "user_id" => session()->id
            );

            $boletaPago->insert($data_boleta);

            $idBoleta = $boletaPago->insertID();

            $pdts = $this->request->getFileMultiple('file_pdt');

            for ($i=0; $i < count($pdts); $i++) { 
                $name = $pdts[$i]->getName();

                $pdts[$i]->move($periodo_anio, $name);

                $archivos_boletas = array(
                    "id_boleta" => $idBoleta,
                    "archivo" => $name,
                    "estado" => 1,
                    "user_id" => session()->id
                );

                $files->insert($archivos_boletas);
            }

            $boletaPago->db->transComplete();

            if ($boletaPago->db->transStatus() === false) {
                throw new \Exception("Error al realizar la operación.");
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se agrego correctamente los archivos"
            ]);

        } catch (\Exception $e) {
            $boletaPago->db->transRollback();

            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
        
    }

    public function consulta()
    {
        $anio_ = new AnioModel();
        $mes_ = new MesModel();
        $boleta = new BoletaPagoModel();

        $ruc = $this->request->getVar("ruc");
        $periodo = $this->request->getVar('periodo');
        $anio = $this->request->getVar('anio');

        $zip = new \ZipArchive();

        $data_anio = $anio_->find($anio);
        $data_periodo = $mes_->find($periodo);

        if (!$data_anio || !$data_periodo) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Datos de año o periodo no encontrados."
            ]);
        }

        $mes = $data_periodo['mes_fecha'];
        $ani = $data_anio['anio_descripcion'];

        $archivo = $ruc . "_" . $mes . $ani . ".zip";
        $zipFileName = FCPATH . 'archivos/boletas_pago/' . $archivo;

        $consulta_archivos = $boleta->query("
            SELECT archivos_boleta.archivo 
            FROM boleta_pago
            INNER JOIN archivos_boleta ON archivos_boleta.id_boleta = boleta_pago.id_boleta
            WHERE boleta_pago.periodo = ? AND boleta_pago.anio = ? AND boleta_pago.ruc_empresa = ?
        ", [$periodo, $anio, $ruc])->getResult();

        if (!$consulta_archivos) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "No hay archivos para el periodo y año seleccionados."
            ]);
        }

        if ($zip->open($zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($consulta_archivos as $value) {
                $filePath = FCPATH . "archivos/boletas_pago/" . $ruc . "/" . $mes . $ani . "/" . $value->archivo;

                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($filePath));
                } else {
                    log_message('error', "Archivo no encontrado: " . $filePath);
                }
            }

            $zip->close();

            return $this->response->setJSON([
                "status" => "success",
                "link" => base_url('archivos/boletas_pago/' . $archivo)
            ]);
        } else {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "No se pudo crear el archivo ZIP"
            ]);
        }
    }

}
