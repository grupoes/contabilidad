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

        $menu = $this->permisos_menu();

        return view('declaraciones/pdtplame', compact('anios', 'meses', 'menu'));
    }

    public function filesSave()
    {
        $pdtPlame = new PdtPlameModel();
        $files = new ArchivosPdtPlameModel();
        $r08 = new R08PlameModel();
        $year = new AnioModel();
        $month = new MesModel();

        $pdtPlame->db->transStart();

        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $file_r01 = $this->request->getFile('file_r01');
            $file_r12 = $this->request->getFile('file_r12');
            $file_constancia = $this->request->getFile('file_constancia');
            $file_r08 = $this->request->getFileMultiple('file_r08');

            $name_r01 = "";
            $name_r12 = "";
            $name_constancia = "";

            $ruc = $data['ruc_empresa'];
            $periodo = $data['periodo'];
            $anio = $data['anio'];

            $consultaPlame = $pdtPlame->where('ruc_empresa', $ruc)->where('periodo', $periodo)->where('anio', $anio)->where('estado', 1)->first();

            if ($consultaPlame) {
                return $this->response->setJSON(['error' => 'success', 'message' => "El periodo y año ya existe."]);
            }

            $hayArchivoR08 = false;

            if (!empty($file_r08)) {
                foreach ($file_r08 as $archivo) {
                    if ($archivo->isValid() && !$archivo->hasMoved()) {
                        $hayArchivoR08 = true;
                        break;
                    }
                }
            }

            // Verificar que al menos uno de los archivos esté presente
            if ((!$file_r01 || !$file_r01->isValid()) && (!$file_r12 || !$file_r12->isValid()) && (!$file_constancia || !$file_constancia->isValid()) && !$hayArchivoR08) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Debe seleccionar al menos un archivo"
                ]);
            }

            $dataAnio = $year->find($anio);
            $dataMes = $month->find($periodo);

            $desPeriodo = strtoupper($dataMes['mes_descripcion']);
            $desAnio = $dataAnio['anio_descripcion'];

            //20329049982_201910_r01.pdf

            if ($file_r01 && $file_r01->isValid()) {
                //$name_r01 = $file_r01->getName();
                $extension_r01 = $file_r01->getExtension();

                $name_r01 = $ruc . '_' . $desAnio . '_' . $desPeriodo . '_r01.' . $extension_r01;
                $file_r01->move(FCPATH . 'archivos/pdt', $name_r01);
            }

            if ($file_r12 && $file_r12->isValid()) {
                //$name_r12 = $file_r12->getName();
                $extension_r12 = $file_r12->getExtension();
                $name_r12 = $ruc . '_' . $desAnio . '_' . $desPeriodo . '_r12.' . $extension_r12;
                $file_r12->move(FCPATH . 'archivos/pdt', $name_r12);
            }

            if ($file_constancia && $file_constancia->isValid()) {
                //$name_r12 = $file_r12->getName();
                $extension_constancia = $file_constancia->getExtension();
                $name_constancia = $ruc . '_' . $desAnio . '_' . $desPeriodo . '_constancia.' . $extension_constancia;

                $file_constancia->move(FCPATH . 'archivos/pdt', $name_constancia);
            }

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

            if ($hayArchivoR08) {
                for ($i = 0; $i < count($file_r08); $i++) {
                    if ($file_r08[$i]->isValid() && !$file_r08[$i]->hasMoved()) {
                        $name_original = $file_r08[$i]->getName();
                        $name = $ruc . '_' . $desAnio . '_' . $desPeriodo . '_' . $name_original;

                        $file_r08[$i]->move(FCPATH . 'archivos/pdt', $name);

                        $data_r08 = array(
                            "plameId" => $pdtPlameId,
                            "nameFile" => $name,
                            "status" => 1,
                            "user_id" => session()->id
                        );

                        $r08->insert($data_r08);
                    } else {
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
                    }
                }
            }

            $pdtPlame->db->transComplete();

            if ($pdtPlame->db->transStatus() === false) {
                throw new \Exception("Error al realizar la operación.");
            }

            return $this->response->setJSON(['status' => 'success', 'message' => "Se guardo correctamente"]);
        } catch (\Exception $e) {
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
        pdt_plame.periodo,pdt_plame.anio,archivos_pdtplame.id_archivos_pdtplame,archivos_pdtplame.archivo_planilla,archivos_pdtplame.archivo_honorarios,archivos_pdtplame.archivo_constancia,archivos_pdtplame.estado,archivos_pdtplame.id_pdtplame,anio.anio_descripcion,mes.mes_descripcion, pdt_plame.id_pdt_plame
        FROM pdt_plame
        INNER JOIN archivos_pdtplame ON archivos_pdtplame.id_pdtplame = pdt_plame.id_pdt_plame
        INNER JOIN anio ON pdt_plame.anio = anio.id_anio
        INNER JOIN mes ON mes.id_mes = pdt_plame.periodo
        WHERE pdt_plame.ruc_empresa = $ruc AND pdt_plame.anio = $anio AND pdt_plame.periodo = $periodo AND archivos_pdtplame.estado = 1")->getRow();

        $rectificar = $this->getPermisosAcciones(9, session()->perfil_id, 'rectificar');
        $eliminar = $this->getPermisosAcciones(9, session()->perfil_id, 'eliminar');

        if ($consulta) {
            $idpdt = $consulta->id_pdt_plame;

            $r08 = new R08PlameModel();

            $consultaR08 = $r08->where('plameId', $idpdt)->where('status', 1)->findAll();

            if ($consultaR08) {
                $consulta->r08 = "1";
            } else {
                $consulta->r08 = "0";
            }

            $acciones = "";

            if ($rectificar) {
                $acciones .= '<button type="button" class="btn btn-info btn-sm" title="Rectificar Archivos" onclick="rectificar(' . $consulta->id_pdtplame . ', ' . $consulta->id_archivos_pdtplame . ', ' . $consulta->periodo . ', ' . $consulta->anio . ', \'' . $consulta->mes_descripcion . '\', ' . $consulta->anio_descripcion . ')">RECT</button> ';
            }

            if ($eliminar) {
                $acciones .= '<button type="button" class="btn btn-danger btn-sm" title="Eliminar Archivos" onclick="eliminar(' . $consulta->id_pdtplame . ', ' . $consulta->id_archivos_pdtplame . ')"><i class="ti ti-trash"></i></button>';
            }

            $consulta->acciones = $acciones;
        }

        return $this->response->setJSON($consulta);
    }

    public function consultaR08($idplame)
    {
        $r08 = new R08PlameModel();

        $consulta = $r08->where('plameId', $idplame)->where('status', 1)->findAll();

        return $this->response->setJSON($consulta);
    }

    public function descargarR08All($id)
    {
        $r08 = new R08PlameModel();

        $consulta = $r08->where('plameId', $id)->where('status', 1)->findAll();

        $zip = new \ZipArchive();
        $zipName = 'r08_' . $id . '.zip';

        $zipPath = FCPATH . 'archivos/pdt/' . $zipName;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return $this->response->setJSON(['error' => 'No se pudo crear el archivo ZIP']);
        }

        foreach ($consulta as $key => $value) {
            $zip->addFile(FCPATH . 'archivos/pdt/' . $value['nameFile'], $value['nameFile']);
        }

        $zip->close();

        return $this->response->download($zipPath, null);
    }

    public function rectificarPlame()
    {
        $files = new ArchivosPdtPlameModel();
        $year = new AnioModel();
        $month = new MesModel();

        try {

            $files->db->transBegin();

            $idplame = $this->request->getVar('idplame');
            $idPlameFiles = $this->request->getVar('idPlameFiles');
            $ruc = $this->request->getVar('ruc');
            $periodo = $this->request->getVar('periodo');
            $anio = $this->request->getVar('anio');

            $file_r01 = $this->request->getFile('file_r01');
            $file_r12 = $this->request->getFile('file_r12');
            $file_constancia = $this->request->getFile('file_constancia');
            $file_r08 = $this->request->getFileMultiple('file_r08');

            $hayArchivoR08 = false;

            if (!empty($file_r08)) {
                foreach ($file_r08 as $archivo) {
                    if ($archivo->isValid() && !$archivo->hasMoved()) {
                        $hayArchivoR08 = true;
                        break;
                    }
                }
            }

            // Verificar que al menos uno de los archivos esté presente
            if ((!$file_r01 || !$file_r01->isValid()) && (!$file_r12 || !$file_r12->isValid()) && (!$file_constancia || !$file_constancia->isValid()) && !$hayArchivoR08) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Debe seleccionar al menos un archivo"
                ]);
            }

            $dataAnio = $year->find($anio);
            $dataMes = $month->find($periodo);
            $dataFiles = $files->find($idPlameFiles);

            $name_r01 = "";
            $name_r12 = "";
            $name_constancia = "";

            $desPeriodo = strtoupper($dataMes['mes_descripcion']);
            $desAnio = $dataAnio['anio_descripcion'];

            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            if ($file_r01 && $file_r01->isValid()) {
                $archivo = trim($dataFiles['archivo_planilla'] ?? '');

                if ($archivo !== '') {
                    $ruta = FCPATH . 'archivos/pdt/' . $archivo;
                    $ruta = str_replace('/', DIRECTORY_SEPARATOR, $ruta);

                    clearstatcache();

                    if (file_exists($ruta)) {
                        unlink($ruta);
                    }
                }

                $extension_r01 = $file_r01->getExtension();
                $name_r01 = $ruc . '_' . $desAnio . '_' . $desPeriodo . '_r01_' . $codigo . '.' . $extension_r01;
                $file_r01->move(FCPATH . 'archivos/pdt', $name_r01);
            } else {
                $name_r01 = $dataFiles['archivo_planilla'];
            }

            if ($file_r12 && $file_r12->isValid()) {

                $archivo_hono = trim($dataFiles['archivo_honorarios'] ?? '');

                if ($archivo_hono !== '') {
                    $ruta_hono = FCPATH . 'archivos/pdt/' . $archivo_hono;
                    $ruta_hono = str_replace('/', DIRECTORY_SEPARATOR, $ruta_hono);

                    clearstatcache();

                    if (file_exists($ruta_hono)) {
                        unlink($ruta_hono);
                    }
                }

                $extension_r12 = $file_r12->getExtension();
                $name_r12 = $ruc . '_' . $desAnio . '_' . $desPeriodo . '_r12_' . $codigo . '.' . $extension_r12;
                $file_r12->move(FCPATH . 'archivos/pdt', $name_r12);
            } else {
                $name_r12 = $dataFiles['archivo_honorarios'];
            }

            if ($file_constancia && $file_constancia->isValid()) {

                $archivo_const = trim($dataFiles['archivo_constancia'] ?? '');

                if ($archivo_const !== '') {
                    $ruta_constancia = FCPATH . 'archivos/pdt/' . $archivo_const;
                    $ruta_constancia = str_replace('/', DIRECTORY_SEPARATOR, $ruta_constancia);

                    clearstatcache();

                    if (file_exists($ruta_constancia)) {
                        unlink($ruta_constancia);
                    }
                }

                $extension_constancia = $file_constancia->getExtension();
                $name_constancia = $ruc . '_' . $desAnio . '_' . $desPeriodo . '_constancia_' . $codigo . '.' . $extension_constancia;
                $file_constancia->move(FCPATH . 'archivos/pdt', $name_constancia);
            } else {
                $name_constancia = $dataFiles['archivo_constancia'];
            }

            $dataUpdate = array(
                "estado" => 0,
            );

            $files->update($idPlameFiles, $dataUpdate);

            $dataArchi = array(
                "archivo_planilla" => $name_r01,
                "archivo_honorarios" => $name_r12,
                "archivo_constancia" => $name_constancia,
                "estado" => 1,
                "user_id" => session()->id,
                "id_pdtplame" => $idplame
            );

            $files->insert($dataArchi);

            if ($hayArchivoR08) {
                $r08 = new R08PlameModel();

                for ($i = 0; $i < count($file_r08); $i++) {
                    if ($file_r08[$i]->isValid() && !$file_r08[$i]->hasMoved()) {

                        $code = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);

                        $name_original = $file_r08[$i]->getName();
                        $name = $code . '_' . $ruc . '_' . $desAnio . '_' . $desPeriodo . '_' . $name_original;

                        $file_r08[$i]->move(FCPATH . 'archivos/pdt', $name);

                        $data_r08 = array(
                            "plameId" => $idplame,
                            "nameFile" => $name,
                            "status" => 1,
                            "user_id" => session()->id
                        );

                        $r08->insert($data_r08);
                    }
                }
            }

            if ($files->db->transStatus() === false) {
                $files->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $files->db->transCommit();

            return $this->response->setJSON([
                "status" => "ok",
                "message" => "archivos rectificados correctamente",
            ]);
        } catch (\Exception $e) {
            $files->db->transRollback();

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al rectificar el plame, ' . $e->getMessage()
            ]);
        }
    }

    public function rectificarR08()
    {
        $r08 = new R08PlameModel();
        $plame = new PdtPlameModel();

        try {
            $idr08 = $this->request->getVar('idR08');

            $file_r08 = $this->request->getFile('file_r08');

            if (!$file_r08 || !$file_r08->isValid()) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Debe seleccionar un archivo"
                ]);
            }

            $dataR08 = $r08->find($idr08);

            $idplame = $dataR08['plameId'];

            $dataPlame = $plame->find($idplame);

            $periodo = $dataPlame['periodo'];
            $anio = $dataPlame['anio'];
            $ruc = $dataPlame['ruc_empresa'];

            $year = new AnioModel();
            $month = new MesModel();

            $dataAnio = $year->find($anio);
            $dataMes = $month->find($periodo);

            $desPeriodo = strtoupper($dataMes['mes_descripcion']);
            $desAnio = $dataAnio['anio_descripcion'];

            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            $name_original = $file_r08->getName();

            $name = $ruc . '_' . $desAnio . '_' . $desPeriodo . '_' . $codigo . '_' . $name_original;

            $file_r08->move(FCPATH . 'archivos/pdt', $name);

            $dataUpdate = array(
                "nameFile" => $name,
                "user_edit" => session()->id,
            );

            $r08->update($idr08, $dataUpdate);

            return $this->response->setJSON([
                "status" => "ok",
                "message" => "archivo rectificado correctamente",
                "idplame" => $idplame
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al rectificar el plame, ' . $e->getMessage()
            ]);
        }
    }

    public function eliminarR08($id)
    {
        $r08 = new R08PlameModel();

        $dataR08 = $r08->find($id);

        $idplame = $dataR08['plameId'];

        $dataUpdate = array(
            "status" => 0,
            "user_delete" => session()->id,
        );

        $r08->update($id, $dataUpdate);

        return $this->response->setJSON([
            "status" => "ok",
            "message" => "archivo eliminado correctamente",
            "idplame" => $idplame
        ]);
    }

    public function eliminar($idPlame, $idarchivo)
    {
        $files = new ArchivosPdtPlameModel();
        $pdtPlame = new PdtPlameModel();
        $r08 = new R08PlameModel();

        try {
            $files->update($idarchivo, [
                'estado' => 0,
                'user_delete' => session()->id
            ]);

            $pdtPlame->update($idPlame, [
                'estado' => 0,
                'user_delete' => session()->id
            ]);

            $r08->set('status', 0);
            $r08->where('plameId', $idPlame);
            $r08->update();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "plame eliminado correctamente",
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al eliminar el plame, ' . $e->getMessage()
            ]);
        }
    }

    public function eliminarAll()
    {
        $r08 = new R08PlameModel();

        try {
            $data = $this->request->getJSON();

            $ids = $data->ids;

            $id = $ids[0];

            $dataR08 = $r08->find($id);

            $idplame = $dataR08['plameId'];

            $r08
                ->whereIn('id', $ids)
                ->set([
                    'status' => 0,
                    'user_delete' => session()->id
                ])
                ->update();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Archivos r08 eliminado correctamente",
                "idplame" => $idplame
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error al eliminar el plame, ' . $e->getMessage()
            ]);
        }
    }
}
