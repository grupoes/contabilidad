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
        $archivosTicket = new ArchivosTicketAfpModel();
        $mes = new MesModel();
        $anio_ = new AnioModel();

        try {

            $files->db->transBegin();

            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $ruc = $data['ruc_empresa'];

            $file_reportes = $this->request->getFileMultiple('file_reporte');
            $file_ticket = $this->request->getFileMultiple('file_ticket');
            $file_plantilla = $this->request->getFile('file_plantilla');

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

            $hayTickets = false;

            if ($ruc != '10438453291' && $ruc != '20542322412' && $ruc != '20603670249') {

                if (!empty($file_ticket)) {
                    foreach ($file_ticket as $archivo) {
                        if ($archivo->isValid() && !$archivo->hasMoved()) {
                            $hayTickets = true;
                            break;
                        }
                    }
                }

                if (!$hayTickets) {
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Debe seleccionar al menos un archivo de ticket"
                    ]);
                }
            }

            if (!$file_plantilla) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de plantilla']);
            }

            if (!$file_plantilla->isValid()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'El archivo de plantilla no es válido']);
            }

            if ($file_plantilla->getClientMimeType() !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permiten archivos Excel']);
            }


            $periodo = $data['periodo'];
            $anio = $data['anio'];
            $idCont = $data['idTabla'];

            $consultaAfp = $afp->where('contribuyente_id', $idCont)->where('periodo', $periodo)->where('anio', $anio)->where('estado', 1)->first();

            if ($consultaAfp) {
                return $this->response->setJSON(['error' => 'success', 'message' => "El periodo y año ya existe."]);
            }

            $data_periodo = $mes->find($periodo);

            $data_anio = $anio_->find($anio);

            $per = strtoupper($data_periodo['mes_descripcion']);
            $ani = $data_anio['anio_descripcion'];

            $ext_plantilla = $file_plantilla->getExtension();

            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);


            $archivo_plantilla = "AFP_PLANTILLA_" . $ruc . "_" . $per . $ani . "_" . $codigo . "." . $ext_plantilla;

            $file_plantilla->move(FCPATH . 'archivos/afp', $archivo_plantilla);

            $datos_pdt = array(
                "contribuyente_id" => $idCont,
                "periodo" => $periodo,
                "anio" => $anio,
                "user_add" => session()->id,
                "estado" => 1
            );

            $afp->insert($datos_pdt);

            $afpId = $afp->insertID();

            $datos_files = array(
                "afp_id" => $afpId,
                "archivo_plantilla" => $archivo_plantilla,
                "estado" => 1,
                "user_add" => session()->id
            );

            $files->insert($datos_files);

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

            if ($ruc != '10438453291' || $ruc != '20542322412' || $ruc != '20603670249') {
                if ($hayTickets) {
                    for ($i = 0; $i < count($file_ticket); $i++) {
                        if ($file_ticket[$i]->isValid() && !$file_ticket[$i]->hasMoved()) {
                            $name_original = $file_ticket[$i]->getName();

                            $code = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

                            $name = $code . '_' . $name_original;

                            $file_ticket[$i]->move(FCPATH . 'archivos/afp', $name);

                            $data_ticket = array(
                                "afp_id" => $afpId,
                                "name_file" => $name,
                                "estado" => 1,
                                "user_add" => session()->id
                            );

                            $archivosTicket->insert($data_ticket);
                        } else {
                            return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
                        }
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
        afp.id as afpId, afp.periodo, afp.anio, afp.contribuyente_id,archivos_afp.id,archivos_afp.archivo_reporte,archivos_afp.archivo_ticket,archivos_afp.archivo_plantilla,archivos_afp.estado,archivos_afp.afp_id,anio.anio_descripcion,mes.mes_descripcion
        FROM afp
        INNER JOIN archivos_afp ON archivos_afp.afp_id = afp.id
        INNER JOIN anio ON afp.anio = anio.id_anio
        INNER JOIN mes ON mes.id_mes = afp.periodo
        WHERE afp.contribuyente_id = $idContribuyente AND afp.anio = $anio AND afp.periodo = $periodo AND archivos_afp.estado = 1")->getResultArray();

        $rectificar = $this->getPermisosAcciones(41, session()->perfil_id, 'rectificar');
        $detalle = $this->getPermisosAcciones(41, session()->perfil_id, 'ver detalle');
        $eliminar = $this->getPermisosAcciones(41, session()->perfil_id, 'eliminar');

        foreach ($consulta as $key => $value) {
            $acciones = "";

            if ($rectificar) {
                $acciones .= '<button type="button" class="btn btn-warning btn-sm" title="Rectificar Archivos" onclick="rectificar(' . $value['afp_id'] . ', ' . $value['id'] . ', ' . $value['periodo'] . ', ' . $value['anio'] . ', ' . $value['contribuyente_id'] . ', \'' . $value['mes_descripcion'] . '\', ' . $value['anio_descripcion'] . ')">RECT</button> ';
            }

            if ($detalle) {
                $acciones .= '<button type="button" class="btn btn-info btn-sm" title="detalle" onclick="details_archivos(' . $value['afp_id'] . ', \'' . $value['mes_descripcion'] . '\', ' . $value['anio_descripcion'] . ')">DET</button> ';
            }

            if ($eliminar) {
                $acciones .= '<button type="button" class="btn btn-danger btn-sm" title="eliminar" onclick="eliminar(' . $value['afp_id'] . ', ' . $value['id'] . ')"><i class="ti ti-trash"></i></button>';
            }

            $consulta[$key]['acciones'] = $acciones;
        }

        return $this->response->setJSON($consulta);
    }

    public function rectificar()
    {
        $mes = new MesModel();
        $anio_ = new AnioModel();
        $files = new ArchivosAfpModel();
        $contribuyente = new ContribuyenteModel();
        $files_reportes = new ArchivosReporteAfpModel();
        $files_tickets = new ArchivosTicketAfpModel();

        try {
            $files->db->transBegin();

            $idafp = $this->request->getVar('idpdtrenta');
            $idarchivo = $this->request->getVar('idarchivos');
            $periodo = $this->request->getVar('periodoRectificacion');
            $anio = $this->request->getVar('anioRectificacion');
            $idContribuyente = $this->request->getVar('rucRect');

            $file1 = $this->request->getFileMultiple('fileReporte');
            $file2 = $this->request->getFileMultiple('fileTicket');
            $file3 = $this->request->getFile('filePlantilla');

            $dataContribuyente = $contribuyente->select('ruc')->find($idContribuyente);
            $ruc = $dataContribuyente['ruc'];

            $hayReportes = false;

            if (!empty($file1)) {
                foreach ($file1 as $archivo) {
                    if ($archivo->isValid() && !$archivo->hasMoved()) {
                        $hayReportes = true;
                        break;
                    }
                }
            }

            $hayTickets = false;

            if ($ruc != '10438453291' && $ruc != '20542322412' && $ruc != '20603670249') {
                if (!empty($file2)) {
                    foreach ($file2 as $archivo) {
                        if ($archivo->isValid() && !$archivo->hasMoved()) {
                            $hayTickets = true;
                            break;
                        }
                    }
                }

                // Verificar que al menos uno de los archivos esté presente
                if (!$hayReportes && !$hayTickets && (!$file3 || !$file3->isValid())) {
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Debe seleccionar al menos un archivo"
                    ]);
                }
            } else {
                // Verificar que al menos uno de los archivos esté presente
                if (!$hayReportes && (!$file3 || !$file3->isValid())) {
                    return $this->response->setJSON([
                        "status" => "error",
                        "message" => "Debe seleccionar al menos un archivo"
                    ]);
                }
            }

            $data_periodo = $mes->find($periodo);

            $data_anio = $anio_->find($anio);

            $per = strtoupper($data_periodo['mes_descripcion']);
            $ani = $data_anio['anio_descripcion'];

            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            $archivo_plantilla = "";

            $dataArchivo = $files->find($idarchivo);

            if ($file3->isValid()) {
                $ext_plantilla = $file3->getExtension();
                $archivo_plantilla = "AFP_PLANILLA_" . $ruc . "_" . $per . $ani . "_RECT_" . $codigo . "." . $ext_plantilla;
                $file3->move(FCPATH . 'archivos/afp', $archivo_plantilla);
            } else {
                $archivo_plantilla = $dataArchivo['archivo_plantilla'];
            }

            $datos_files = array(
                "afp_id" => $idafp,
                "archivo_plantilla" => $archivo_plantilla,
                "estado" => 1,
                "user_add" => session()->id
            );

            $files->insert($datos_files);

            $files->update($idarchivo, array(
                "estado" => 0,
                "user_edit" => session()->id
            ));

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

            if ($ruc != '10438453291' && $ruc != '20542322412' && $ruc != '20603670249') {
                if ($hayTickets) {
                    $files_tickets
                        ->set('estado', 0)
                        ->set('user_edit', session()->id)
                        ->where('afp_id', $idarchivo)
                        ->update();

                    for ($i = 0; $i < count($file2); $i++) {
                        if ($file2[$i]->isValid() && !$file2[$i]->hasMoved()) {
                            $name_original = $file2[$i]->getName();

                            $code = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

                            $name = $code . '_' . $name_original;

                            $file2[$i]->move(FCPATH . 'archivos/afp', $name);

                            $data_tickets = array(
                                "afp_id" => $idafp,
                                "name_file" => $name,
                                "estado" => 1,
                                "user_add" => session()->id
                            );

                            $files_tickets->insert($data_tickets);
                        } else {
                            return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
                        }
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

    public function getArchivosTicket($id)
    {
        $files = new ArchivosTicketAfpModel();

        $data = $files->where('afp_id', $id)->where('estado', 1)->findAll();

        return $this->response->setJSON($data);
    }
}
