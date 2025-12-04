<?php

namespace App\Controllers;

use App\Models\AfpModel;
use App\Models\AnioModel;
use App\Models\MesModel;
use App\Models\ArchivosAfpModel;
use App\Models\ArchivosReporteAfpModel;
use App\Models\ArchivosTicketAfpModel;
use App\Models\ContribuyenteModel;
use App\Models\FechaDeclaracionModel;

class Afp extends BaseController
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

        //$crear = $this->getPermisosAcciones(18, session()->perfil_id, 'crear');

        return view('declaraciones/afp', compact('menu', 'anios', 'meses'));
    }

    public function save()
    {
        $afp = new AfpModel();
        $files = new ArchivosAfpModel();
        $archivosReporte = new ArchivosReporteAfpModel();

        try {

            $files->db->transBegin();

            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $file_reportes = $this->request->getFileMultiple('file_reporte');
            $file_plantilla = $this->request->getFileMultiple('file_plantilla');

            $hayReportes = false;

            if (!empty($file_reportes)) {
                foreach ($file_reportes as $archivo) {
                    if ($archivo->isValid() && !$archivo->hasMoved()) {
                        $hayReportes = true;
                        break;
                    }
                }
            }

            if (!$hayReportes) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Debe seleccionar al menos un archivo de reporte"
                ]);
            }

            $hayPlantilla = false;

            if (!empty($file_plantilla)) {
                foreach ($file_plantilla as $archivo) {
                    if ($archivo->isValid() && !$archivo->hasMoved()) {
                        $hayPlantilla = true;
                        break;
                    }
                }
            }

            if (!$hayPlantilla) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Debe seleccionar al menos un archivo de plantilla"
                ]);
            }

            $periodo = $data['periodo'];
            $anio = $data['anio'];
            $idCont = $data['idTabla'];

            $consultaAfp = $afp->where('contribuyente_id', $idCont)->where('periodo', $periodo)->where('anio', $anio)->where('estado', 1)->first();

            if ($consultaAfp) {
                return $this->response->setJSON(['error' => 'success', 'message' => "El periodo y año ya existe."]);
            }

            $datos_pdt = array(
                "contribuyente_id" => $idCont,
                "periodo" => $periodo,
                "anio" => $anio,
                "user_add" => session()->id,
                "estado" => 1
            );

            $afp->insert($datos_pdt);

            $afpId = $afp->insertID();

            if ($hayPlantilla) {
                for ($i = 0; $i < count($file_plantilla); $i++) {
                    if ($file_plantilla[$i]->isValid() && !$file_plantilla[$i]->hasMoved()) {
                        $name_original = $file_plantilla[$i]->getName();

                        $code = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

                        $name = $code . '_' . $name_original;

                        $file_plantilla[$i]->move(FCPATH . 'archivos/afp', $name);

                        $data_plantilla = array(
                            "afp_id" => $afpId,
                            "archivo_plantilla" => $name,
                            "estado" => 1,
                            "user_add" => session()->id
                        );

                        $files->insert($data_plantilla);
                    } else {
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
                    }
                }
            }

            if ($hayReportes) {
                for ($i = 0; $i < count($file_reportes); $i++) {
                    if ($file_reportes[$i]->isValid() && !$file_reportes[$i]->hasMoved()) {
                        $name_original = $file_reportes[$i]->getName();

                        $code = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

                        $name = $code . '_' . $name_original;

                        $file_reportes[$i]->move(FCPATH . 'archivos/afp', $name);

                        $data_reporte = array(
                            "afp_id" => $afpId,
                            "name_file" => $name,
                            "estado" => 1,
                            "user_add" => session()->id
                        );

                        $archivosReporte->insert($data_reporte);
                    } else {
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
                    }
                }
            }

            if ($files->db->transStatus() === false) {
                $files->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $files->db->transCommit();

            return $this->response->setJSON(['status' => 'success', 'message' => "Se registro correctamente"]);
        } catch (\Exception $e) {
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function consulta()
    {
        $afp = new AfpModel();

        $periodo = $this->request->getVar('periodo');
        $anio = $this->request->getVar('anio');
        $idContribuyente = $this->request->getVar('contribuyente_id');

        $consulta = $afp->query("SELECT
        afp.id as afpId, afp.periodo, afp.anio, afp.contribuyente_id,anio.anio_descripcion,mes.mes_descripcion
        FROM afp
        INNER JOIN anio ON afp.anio = anio.id_anio
        INNER JOIN mes ON mes.id_mes = afp.periodo
        WHERE afp.contribuyente_id = $idContribuyente AND afp.anio = $anio AND afp.periodo = $periodo AND afp.estado = 1")->getRowArray();

        $rectificar = $this->getPermisosAcciones(41, session()->perfil_id, 'rectificar');
        $detalle = $this->getPermisosAcciones(41, session()->perfil_id, 'ver detalle');
        $eliminar = $this->getPermisosAcciones(41, session()->perfil_id, 'eliminar');

        $acciones = "";

        if ($consulta) {
            if ($rectificar) {
                $acciones .= '<button type="button" class="btn btn-warning btn-sm" title="Rectificar Archivos" onclick="rectificar(' . $consulta['afpId'] . ', ' . $consulta['periodo'] . ', ' . $consulta['anio'] . ', ' . $consulta['contribuyente_id'] . ', \'' . $consulta['mes_descripcion'] . '\', ' . $consulta['anio_descripcion'] . ')">RECT</button> ';
            }

            if ($detalle) {
                $acciones .= '<button type="button" class="btn btn-info btn-sm" title="detalle" onclick="details_archivos(' . $consulta['afpId'] . ', \'' . $consulta['mes_descripcion'] . '\', ' . $consulta['anio_descripcion'] . ')">DET</button> ';
            }

            if ($eliminar) {
                $acciones .= '<button type="button" class="btn btn-danger btn-sm" title="eliminar" onclick="eliminar(' . $consulta['afpId'] . ')"><i class="ti ti-trash"></i></button>';
            }

            $consulta['acciones'] = $acciones;
        }

        return $this->response->setJSON($consulta);
    }

    public function rectificar()
    {

        $files = new ArchivosAfpModel();
        $contribuyente = new ContribuyenteModel();
        $files_reportes = new ArchivosReporteAfpModel();

        try {
            $files->db->transBegin();

            $idafp = $this->request->getVar('idpdtrenta');
            $idarchivo = $this->request->getVar('idarchivos');
            $idContribuyente = $this->request->getVar('rucRect');

            $file1 = $this->request->getFileMultiple('fileReporte');
            $file3 = $this->request->getFileMultiple('filePlantilla');

            $hayReportes = false;

            if (!empty($file1)) {
                foreach ($file1 as $archivo) {
                    if ($archivo->isValid() && !$archivo->hasMoved()) {
                        $hayReportes = true;
                        break;
                    }
                }
            }

            $hayPlantilla = false;

            if (!empty($file3)) {
                foreach ($file3 as $archivo) {
                    if ($archivo->isValid() && !$archivo->hasMoved()) {
                        $hayPlantilla = true;
                        break;
                    }
                }
            }

            // Verificar que al menos uno de los archivos esté presente
            if (!$hayReportes  && !$hayPlantilla) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Debe seleccionar al menos un archivo"
                ]);
            }

            if ($hayReportes) {

                $files_reportes
                    ->set('estado', 0)
                    ->set('user_edit', session()->id)
                    ->where('afp_id', $idarchivo)
                    ->update();

                for ($i = 0; $i < count($file1); $i++) {
                    if ($file1[$i]->isValid() && !$file1[$i]->hasMoved()) {
                        $name_original = $file1[$i]->getName();

                        $code = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

                        $name = $code . '_' . $name_original;

                        $file1[$i]->move(FCPATH . 'archivos/afp', $name);

                        $data_reporte = array(
                            "afp_id" => $idafp,
                            "name_file" => $name,
                            "estado" => 1,
                            "user_add" => session()->id
                        );

                        $files_reportes->insert($data_reporte);
                    } else {
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
                    }
                }
            }

            if ($hayPlantilla) {
                $files
                    ->set('estado', 0)
                    ->set('user_edit', session()->id)
                    ->where('afp_id', $idarchivo)
                    ->update();

                for ($i = 0; $i < count($file3); $i++) {
                    if ($file3[$i]->isValid() && !$file3[$i]->hasMoved()) {
                        $name_original = $file3[$i]->getName();

                        $code = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

                        $name = $code . '_' . $name_original;

                        $file3[$i]->move(FCPATH . 'archivos/afp', $name);

                        $data_plantilla = array(
                            "afp_id" => $idafp,
                            "archivo_plantilla" => $name,
                            "estado" => 1,
                            "user_add" => session()->id
                        );

                        $files->insert($data_plantilla);
                    } else {
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
                    }
                }
            }


            if ($files->db->transStatus() === false) {
                $files->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $files->db->transCommit();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se registro correctamente"
            ]);
        } catch (\Exception $e) {
            $files->db->transRollback();
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrio un error " . $e->getMessage()
            ]);
        }
    }

    public function getArchivos($id_afp)
    {
        $files = new ArchivosAfpModel();

        $data = $files->where('afp_id', $id_afp)->orderBy('id', 'desc')->findAll();

        return $this->response->setJSON($data);
    }

    public function delete($id_afp, $id_archivo)
    {
        $files = new ArchivosAfpModel();
        $afp = new AfpModel();

        try {
            $files->update($id_archivo, array(
                "estado" => 0,
                "user_delete" => session()->id,
                "deleted_at" => date('Y-m-d H:i:s')
            ));

            $afp->update($id_afp, array(
                "estado" => 0,
                "user_delete" => session()->id,
                "deleted_at" => date('Y-m-d H:i:s')
            ));

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se elimino correctamente"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrio un error " . $e->getMessage()
            ]);
        }
    }

    public function consultaAfpRango()
    {
        $afp = new AfpModel();

        $anio = $this->request->getVar('anio_consulta');
        $desde = $this->request->getVar('desde');
        $hasta = $this->request->getVar('hasta');
        $idcont = $this->request->getVar('idcont');

        if ($desde > $hasta) {

            return $this->response->setJSON([
                "status" => "error",
                "message" => "La fecha de Inicio (desde) no puede ser mayor a la fecha final (hasta)"
            ]);
        }

        $data = $afp->query("SELECT * from afp inner join mes ON mes.id_mes = afp.periodo inner join archivos_afp ON afp.id = archivos_afp.afp_id where afp.contribuyente_id = '$idcont' and afp.anio = $anio and archivos_afp.estado = 1 and afp.periodo BETWEEN '$desde' and '$hasta'")->getResult();

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Consulta correctamente",
            "data" => $data
        ]);
    }

    public function notificar_afp_all()
    {
        $data = $this->notificar_afp();

        return $this->response->setJSON($data);
    }

    public function getArchivosReporte($id)
    {
        $files = new ArchivosReporteAfpModel();

        $data = $files->where('afp_id', $id)->where('estado', 1)->findAll();

        return $this->response->setJSON($data);
    }

    public function getArchivosPlantilla($id)
    {
        $files = new ArchivosAfpModel();

        $data = $files->where('afp_id', $id)->where('estado', 1)->findAll();

        return $this->response->setJSON($data);
    }
}
