<?php

namespace App\Controllers;

use App\Models\SedeModel;
use App\Models\UitModel;
use App\Models\TributoModel;
use App\Models\ContadorModel;
use App\Models\AnioModel;

use App\Models\PdtRentaModel;

class Configuracion extends BaseController
{
    public function cajaVirtual()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $sede = new SedeModel();

        $sedes = $sede->where('estado', 1)->findAll();

        return view('configuracion/cajaVirtual', compact('sedes'));
    }

    public function saveCajaVirtual()
    {
        $sede = new SedeModel();

        try {
            $sede_id = $this->request->getVar('sede_id');

            $data_update = array(
                "caja_virtual" => 0
            );

            $sede->set($data_update)->where('1=1')->update();

            $data_new = array(
                "caja_virtual" => 1
            );

            $sede->update($sede_id, $data_new);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se configuro correctamemte"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function Uit()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $uit = new UitModel();

        $monto_uit = $uit->first();

        return view('configuracion/uit', compact('monto_uit'));
    }

    public function saveUit()
    {
        try {
            $uit = new UitModel();

            $id = $this->request->getVar('id');
            $monto = $this->request->getVar('uit');

            $data = array(
                "uit_monto" => $monto
            );

            if ($id) {
                $uit->update($id, $data);
            } else {
                $uit->insert($data);
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente"
            ]);
        } catch (\Exception $e) {
            //throw $th;
        }
    }

    public function renta()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $tributo = new TributoModel();

        $rentas = $tributo->where('tri_codigo', 3081)->findAll();

        return view('configuracion/renta', compact('rentas'));
    }

    public function contadores()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        return view('configuracion/contadores');
    }

    public function renderContadores()
    {
        $contador = new ContadorModel();

        $contadores = $contador->where('estado !=', 0)->findAll();

        return $this->response->setJSON($contadores);
    }

    public function elegirContador($id)
    {
        $contador = new ContadorModel();

        $data = array(
            "estado" => 1
        );

        $contador->set($data)
            ->where('estado !=', 0)
            ->update();

        $contador->update($id, ["estado" => 2]);

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Se eligio correctamente"
        ]);
    }

    public function sendFileGoogleCloudStorage()
    {
        try {
            $anio = $this->request->getVar('anio');
            $desde = $this->request->getVar('desde');
            $hasta = $this->request->getVar('hasta');
            $ruc = $this->request->getVar('empresa_ruc');

            $pdtRenta = new PdtRentaModel();
            $anioModel = new AnioModel();

            $consulta = $pdtRenta->query("SELECT * from pdt_renta inner join mes ON mes.id_mes = pdt_renta.periodo inner join archivos_pdt0621 ON pdt_renta.id_pdt_renta = archivos_pdt0621.id_pdt_renta where pdt_renta.ruc_empresa = '$ruc' and pdt_renta.anio = $anio and archivos_pdt0621.estado = 1 and pdt_renta.periodo BETWEEN '$desde' and '$hasta'")->getResult();

            $dataAnio = $anioModel->where('id_anio', $anio)->first();

            $links = [];

            $meses = [];

            foreach ($consulta as $key => $value) {
                array_push($links, base_url() . 'archivos/pdt/' . $value->nombre_pdt);
                array_push($links, base_url() . 'archivos/pdt/' . $value->nombre_constancia);

                array_push($meses, $value->mes_descripcion);
            }

            $data = json_encode(["links" => $links]);

            $url_api = "https://esconsultoresyasesores.com:4000/sendFilesGoogleCloudStorage";

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url_api,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $datos = array(
                "meses" => $meses,
                "links" => json_decode($response, true),
                "anio" => $dataAnio['anio_descripcion']
            );

            return $this->response->setJSON($datos);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrio un error " . $e->getMessage()
            ]);
        }
    }
}
