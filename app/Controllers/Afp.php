<?php

namespace App\Controllers;

use App\Models\AfpModel;
use App\Models\AnioModel;
use App\Models\MesModel;
use App\Models\ArchivosAfpModel;
use App\Models\ContribuyenteModel;

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
        $mes = new MesModel();
        $anio_ = new AnioModel();

        try {

            $files->db->transBegin();

            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $file_reporte = $this->request->getFile('file_reporte');
            $file_ticket = $this->request->getFile('file_ticket');
            $file_plantilla = $this->request->getFile('file_plantilla');

            if (!$file_reporte) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de reporte']);
            }

            if (!$file_ticket) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de ticket']);
            }

            if (!$file_plantilla) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de plantilla']);
            }

            if (!$file_reporte->isValid() || !$file_ticket->isValid() || !$file_plantilla->isValid()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
            }

            if ($file_reporte->getClientMimeType() !== 'application/pdf' || $file_ticket->getClientMimeType() !== 'application/pdf') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permiten archivos PDF']);
            }

            if ($file_plantilla->getClientMimeType() !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permiten archivos Excel']);
            }

            $ruc = $data['ruc_empresa'];
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

            $ext_reporte = $file_reporte->getExtension();
            $ext_ticket = $file_ticket->getExtension();
            $ext_plantilla = $file_plantilla->getExtension();

            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            $archivo_reporte = "AFP_REPORTE_" . $ruc . "_" . $per . $ani . "_" . $codigo . "." . $ext_reporte;
            $archivo_ticket = "AFP_TICKET_" . $ruc . "_" . $per . $ani . "_"     . $codigo . "." . $ext_ticket;
            $archivo_plantilla = "AFP_PLANTILLA_" . $ruc . "_" . $per . $ani . "_" . $codigo . "." . $ext_plantilla;

            $file_reporte->move(FCPATH . 'archivos/afp', $archivo_reporte);
            $file_ticket->move(FCPATH . 'archivos/afp', $archivo_ticket);
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
                "archivo_reporte" => $archivo_reporte,
                "archivo_ticket" => $archivo_ticket,
                "archivo_plantilla" => $archivo_plantilla,
                "estado" => 1,
                "user_add" => session()->id
            );

            $files->insert($datos_files);

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
        afp.periodo,afp.anio, afp.contribuyente_id,archivos_afp.id,archivos_afp.archivo_reporte,archivos_afp.archivo_ticket,archivos_afp.archivo_plantilla,archivos_afp.estado,archivos_afp.afp_id,anio.anio_descripcion,mes.mes_descripcion
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

        try {
            $files->db->transBegin();

            $idafp = $this->request->getVar('idpdtrenta');
            $idarchivo = $this->request->getVar('idarchivos');
            $periodo = $this->request->getVar('periodoRectificacion');
            $anio = $this->request->getVar('anioRectificacion');
            $idContribuyente = $this->request->getVar('rucRect');

            $file1 = $this->request->getFile('fileReporte');
            $file2 = $this->request->getFile('fileTicket');
            $file3 = $this->request->getFile('filePlantilla');

            // Verificar que al menos uno de los archivos esté presente
            if ((!$file1 || !$file1->isValid()) && (!$file2 || !$file2->isValid()) && (!$file3 || !$file3->isValid())) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Debe seleccionar al menos un archivo"
                ]);
            }

            $dataContribuyente = $contribuyente->select('ruc')->find($idContribuyente);

            $data_periodo = $mes->find($periodo);

            $data_anio = $anio_->find($anio);

            $per = strtoupper($data_periodo['mes_descripcion']);
            $ani = $data_anio['anio_descripcion'];
            $ruc = $dataContribuyente['ruc'];

            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            $archivo_reporte = "";
            $archivo_ticket = "";
            $archivo_plantilla = "";

            $dataArchivo = $files->find($idarchivo);

            if ($file1->isValid()) {
                $ext_reporte = $file1->getExtension();
                $archivo_reporte = "AFP_REPORTE_" . $ruc . "_" . $per . $ani . "_RECT_" . $codigo . "." . $ext_reporte;
                $file1->move(FCPATH . 'archivos/afp', $archivo_reporte);
            } else {
                $archivo_reporte = $dataArchivo['archivo_reporte'];
            }

            if ($file2->isValid()) {
                $ext_ticket = $file2->getExtension();
                $archivo_ticket = "AFP_TICKET_" . $ruc . "_" . $per . $ani . "_RECT_" . $codigo . "." . $ext_ticket;
                $file2->move(FCPATH . 'archivos/afp', $archivo_ticket);
            } else {
                $archivo_ticket = $dataArchivo['archivo_ticket'];
            }

            if ($file3->isValid()) {
                $ext_plantilla = $file3->getExtension();
                $archivo_plantilla = "AFP_PLANILLA_" . $ruc . "_" . $per . $ani . "_RECT_" . $codigo . "." . $ext_plantilla;
                $file3->move(FCPATH . 'archivos/afp', $archivo_plantilla);
            } else {
                $archivo_plantilla = $dataArchivo['archivo_plantilla'];
            }

            $datos_files = array(
                "afp_id" => $idafp,
                "archivo_reporte" => $archivo_reporte,
                "archivo_ticket" => $archivo_ticket,
                "archivo_plantilla" => $archivo_plantilla,
                "estado" => 1,
                "user_add" => session()->id
            );

            $files->insert($datos_files);

            $files->update($idarchivo, array(
                "estado" => 0,
                "user_edit" => session()->id
            ));

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
}
