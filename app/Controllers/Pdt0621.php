<?php

namespace App\Controllers;

use App\Models\AnioModel;
use App\Models\MesModel;
use App\Models\PdtRentaModel;
use App\Models\ArchivosPdt0621Model;

class Pdt0621 extends BaseController
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

        return view('declaraciones/pdt0621', compact('anios', 'meses'));
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

            if ($consultaRenta) {
                return $this->response->setJSON(['error' => 'success', 'message' => "El periodo y año ya existe."]);
            }

            $data_periodo = $mes->find($periodo);

            $data_anio = $anio_->find($anio);

            $per = strtoupper($data_periodo['mes_descripcion']);
            $ani = $data_anio['anio_descripcion'];

            $ext_renta = $file_renta->getExtension();
            $ext_constancia = $file_constancia->getExtension();

            $archivo_pdt = "PDT0621_" . $ruc . "_" . $per . $ani . "." . $ext_renta;
            $archivo_constancia = "CONST_" . $ruc . "_" . $per . $ani . "." . $ext_constancia;

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

    public function consulta()
    {
        $pdtRenta = new PdtRentaModel();

        $periodo = $this->request->getVar('periodo');
        $anio = $this->request->getVar('anio');
        $ruc = $this->request->getVar('ruc');

        $consulta = $pdtRenta->query("SELECT
        pdt_renta.periodo,pdt_renta.anio,archivos_pdt0621.id_archivos_pdt,archivos_pdt0621.nombre_pdt,archivos_pdt0621.nombre_constancia,archivos_pdt0621.estado,archivos_pdt0621.id_pdt_renta,anio.anio_descripcion,mes.mes_descripcion
        FROM pdt_renta
        INNER JOIN archivos_pdt0621 ON archivos_pdt0621.id_pdt_renta = pdt_renta.id_pdt_renta
        INNER JOIN anio ON pdt_renta.anio = anio.id_anio
        INNER JOIN mes ON mes.id_mes = pdt_renta.periodo
        WHERE pdt_renta.ruc_empresa = $ruc AND pdt_renta.anio = $anio AND pdt_renta.periodo = $periodo AND archivos_pdt0621.estado = 1")->getResult();

        return $this->response->setJSON($consulta);
    }

    public function consultaPdt()
    {
        $pdtRenta = new PdtRentaModel();

        $anio = $this->request->getVar('anio_consulta');
        $desde = $this->request->getVar('desde');
        $hasta = $this->request->getVar('hasta');
        $ruc = $this->request->getVar('empresa_ruc');

        if ($desde > $hasta) {

            return $this->response->setJSON([
                "status" => "error",
                "message" => "La fecha de Inicio (desde) no puede ser mayor a la fecha final (hasta)"
            ]);
        }

        $data = $pdtRenta->query("SELECT * from pdt_renta inner join mes ON mes.id_mes = pdt_renta.periodo inner join archivos_pdt0621 ON pdt_renta.id_pdt_renta = archivos_pdt0621.id_pdt_renta where pdt_renta.ruc_empresa = '$ruc' and pdt_renta.anio = $anio and archivos_pdt0621.estado = 1 and pdt_renta.periodo BETWEEN '$desde' and '$hasta'")->getResult();

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Consulta correctamente",
            "data" => $data
        ]);
    }

    public function sendMessageFiles()
    {
        try {
            $anio = $this->request->getVar('anio');
            $numero = $this->request->getVar('numero');
            $links = json_decode($this->request->getVar('links'), true);
            $meses = json_decode($this->request->getVar('meses'), true);

            $detalle = "";

            $contPdt = 0;
            $contConst = 1;

            for ($i = 0; $i < count($meses); $i++) {

                $pdt = $links[$contPdt]['url'];
                $constancia = $links[$contConst]['url'];

                $detalle .= "*" . $meses[$i] . " " . $anio . "*\n" .
                    "pdt: " . $pdt . "\n" .
                    "constancia: " . $constancia . "\n\n";

                $contPdt = $contPdt + 2;
                $contConst = $contConst + 2;
            }

            $mensaje = "Se adjunta los archivos PDT 0621 de los siguientes periodos del año " . $anio . "\n\n" .
                $detalle;

            $data = [
                "number" => "51" . $numero,
                "message" => $mensaje,
                "mediaUrl" => ""
            ];

            // Convertir el array a JSON
            $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Verificar si el JSON es válido antes de enviarlo
            if (json_last_error() !== JSON_ERROR_NONE) {
                die("Error en JSON: " . json_last_error_msg());
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://64.23.188.190:3002/send-message",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $jsonData,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrio un error " . $e->getMessage()
            ]);
        }
    }
}
