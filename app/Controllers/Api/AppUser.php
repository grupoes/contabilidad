<?php

namespace App\Controllers\Api;

use App\Models\AnioModel;
use CodeIgniter\RESTful\ResourceController;
use App\Models\R08PlameModel;
use App\Models\ContribuyenteModel;
use App\Models\MesModel;
use Exception;

class AppUser extends ResourceController
{
    protected $format = 'json';

    public function empresas()
    {
        try {
            $data = $this->request->getJSON(true);

            $usuario = $data['usuario'];

            $r08 = new R08PlameModel();
            $empresa = new ContribuyenteModel();

            $consulta_rucs = $r08->query("SELECT ruc FROM `r08_plame` where numero_documento = '$usuario' GROUP BY ruc")->getResultArray();

            foreach ($consulta_rucs as $key => $value) {
                $ruc = $value['ruc'];

                $datos_empresa = $empresa->select('id, ruc, razon_social, direccion_fiscal')->where('ruc', $ruc)->first();

                $consulta_rucs[$key]['datos_empresa'] = $datos_empresa;
            }

            return $this->respond([
                'status' => true,
                'message' => 'Empresas encontradas',
                'empresas' => $consulta_rucs
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al obtener las empresas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAnios()
    {
        try {
            $anio = new AnioModel();
            $anioActual = date('Y');
            $anios = $anio->where('anio_descripcion >=', 2025)->where('anio_descripcion <=', $anioActual)->orderBy('anio_descripcion', 'DESC')->findAll();

            return $this->respond([
                'status' => true,
                'message' => 'Años encontrados',
                'anios' => $anios
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al obtener los años: ' . $e->getMessage()
            ], 500);
        }
    }

    public function itemBoletas()
    {
        try {
            $datos = $this->request->getJSON(true);

            $ruc = $datos['ruc'];
            $anio = $datos['anio'];
            $mes = $datos['mes'];
            $usuario = $datos['usuario'];

            $r08 = new R08PlameModel();
            $year = new AnioModel();
            $month = new MesModel();

            $anio_actual = date('Y');

            if ($anio == 0) {
                $anios = $year->where('anio_descripcion >=', 2025)->where('anio_descripcion <=', $anio_actual)->orderBy('anio_descripcion', 'DESC')->findAll();
            } else {
                $anios = [$year->find($anio)];
            }

            if ($mes == 0) {
                $sqlMeses = "";
            } else {
                $sqlMeses = "AND p.periodo = $mes";
            }

            foreach ($anios as $key => $value) {
                $idanio = $value['id_anio'];
                $boletas = $r08->query("SELECT r.nameFile, m.mes_descripcion FROM r08_plame r INNER JOIN pdt_plame p ON p.id_pdt_plame = r.plameId INNER JOIN mes m ON m.id_mes = p.periodo WHERE r.ruc = '$ruc' AND p.anio = $idanio $sqlMeses AND r.numero_documento = '$usuario' AND p.estado = 1 AND r.status = 1 ORDER BY p.periodo DESC")->getResultArray();

                $anios[$key]['boletas'] = $boletas;
            }

            return $this->respond([
                'status' => true,
                'message' => 'Boletas encontradas',
                'boletas' => $anios
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al obtener las boletas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getEmpresa($ruc)
    {
        try {
            $empresaModel = new ContribuyenteModel();
            $empresa = $empresaModel->where('ruc', $ruc)->first();

            if (!$empresa) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Empresa no encontrada'
                ], 404);
            }

            return $this->respond([
                'status' => true,
                'message' => 'Empresa encontrada',
                'empresa' => $empresa
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al obtener la empresa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadSelloFirma()
    {
        try {
            $file = $this->request->getFile('imagen');
            $ruc = $this->request->getPost('ruc');

            return $this->respond([
                'status' => true,
                'message' => 'RUC recibido',
                'ruc' => $ruc
            ]);

            if (!$file || !$file->isValid()) {
                return $this->respond([
                    'status' => false,
                    'message' => 'No se recibió ninguna imagen'
                ], 400);
            }

            // Validar tipo y tamaño (solo PNG)
            $validation = \Config\Services::validation();
            $validation->setRules([
                'imagen' => [
                    'label' => 'Imagen',
                    'rules' => 'uploaded[imagen]|max_size[imagen,2048]|is_image[imagen]|mime_in[imagen,image/png]'
                ]
            ]);


            if (!$validation->withRequest($this->request)->run()) {
                return $this->respond([
                    'status' => false,
                    'errors' => implode(' | ', $validation->getErrors())
                ], 400);
            }

            // Generar nombre aleatorio
            $newName = $ruc . "_" . $file->getRandomName();

            // Guardar en public/uploads
            $file->move(FCPATH . 'archivos/sellos', $newName);

            $contri = new ContribuyenteModel();
            $data_update = [
                'file_sello_firma' => $newName
            ];

            $contri->updateWhere(['ruc' => $ruc], $data_update);

            return $this->respond([
                'status' => true,
                'message' => 'Imagen guardada correctamente',
                'filename' => $newName
            ]);
        } catch (Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error al guardar la imagen: ' . $e->getMessage()
            ], 500);
        }
    }
}
