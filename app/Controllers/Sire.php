<?php

namespace App\Controllers;

use App\Models\SireModel;
use App\Models\AnioModel;
use App\Models\MesModel;
use App\Models\ArchivosSireModel;
use App\Models\ContribuyenteModel;
use App\Models\ArchivoTextZipSireModel;
use App\Models\FechaDeclaracionModel;

class Sire extends BaseController
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

        return view('declaraciones/sire', compact('menu', 'anios', 'meses'));
    }

    public function save()
    {
        $sire = new SireModel();
        $files = new ArchivosSireModel();
        $mes = new MesModel();
        $anio_ = new AnioModel();
        $txtZip = new ArchivoTextZipSireModel();

        try {

            $files->db->transBegin();

            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $constancia_ventas = $this->request->getFile('constancia_ventas');
            $constancia_compras = $this->request->getFile('constancia_compras');
            $detalle_preliminar = $this->request->getFile('detalle_preliminar');
            $archivos = $this->request->getFileMultiple('archivos');

            $archivo_detalle_preliminar = "";

            if (!$constancia_ventas) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de constancia de ventas']);
            }

            if (!$constancia_compras) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de constancia de compras']);
            }

            if (!$constancia_ventas->isValid() || !$constancia_compras->isValid()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Uno o ambos archivos no son válidos']);
            }

            if ($constancia_ventas->getClientMimeType() !== 'application/pdf' || $constancia_compras->getClientMimeType() !== 'application/pdf') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permiten archivos PDF']);
            }

            $ruc = $data['ruc_empresa'];
            $periodo = $data['periodo'];
            $anio = $data['anio'];
            $idCont = $data['idTabla'];

            $consultaSire = $sire->where('contribuyente_id', $idCont)->where('periodo', $periodo)->where('anio', $anio)->where('estado', 1)->first();

            if ($consultaSire) {
                return $this->response->setJSON(['error' => 'success', 'message' => "El periodo y año ya existe."]);
            }

            $data_periodo = $mes->find($periodo);

            $data_anio = $anio_->find($anio);

            $per = strtoupper($data_periodo['mes_descripcion']);
            $ani = $data_anio['anio_descripcion'];

            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            if ($detalle_preliminar && $detalle_preliminar->isValid()) {
                $ext_detalle_preliminar = $detalle_preliminar->getExtension();
                $archivo_detalle_preliminar = "DETALLE_PRELIMINAR_" . $ruc . "_" . $per . $ani . "_" . $codigo . "." . $ext_detalle_preliminar;
                $detalle_preliminar->move(FCPATH . 'archivos/sire', $archivo_detalle_preliminar);
            }

            $ext_constancia_ventas = $constancia_ventas->getExtension();
            $ext_constancia_compras = $constancia_compras->getExtension();

            $archivo_constancia_ventas = "CONST_VENTAS_" . $ruc . "_" . $per . $ani . "_" . $codigo . "." . $ext_constancia_ventas;
            $archivo_constancia_compras = "CONST_COMPRAS_" . $ruc . "_" . $per . $ani . "_"     . $codigo . "." . $ext_constancia_compras;


            $constancia_ventas->move(FCPATH . 'archivos/sire', $archivo_constancia_ventas);
            $constancia_compras->move(FCPATH . 'archivos/sire', $archivo_constancia_compras);


            $datos_pdt = array(
                "contribuyente_id" => $idCont,
                "periodo" => $periodo,
                "anio" => $anio,
                "user_add" => session()->id,
                "estado" => 1
            );

            $sire->insert($datos_pdt);

            $sireId = $sire->insertID();

            $datos_files = array(
                "sire_id" => $sireId,
                "constancia_ventas" => $archivo_constancia_ventas,
                "constancia_compras" => $archivo_constancia_compras,
                "detalle_preliminar" => $archivo_detalle_preliminar,
                "estado" => 1,
                "user_add" => session()->id
            );

            $files->insert($datos_files);

            for ($i = 0; $i < count($archivos); $i++) {

                $name_file = $sireId . "_" . $archivos[$i]->getName();

                $archivos[$i]->move(FCPATH . 'archivos/sire', $name_file);

                $datos_files_txt_zip = array(
                    "sire_id" => $sireId,
                    "name_file" => $name_file,
                    "estado" => 1,
                    "user_add" => session()->id
                );

                $txtZip->insert($datos_files_txt_zip);
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
        $sire = new SireModel();

        $periodo = $this->request->getVar('periodo');
        $anio = $this->request->getVar('anio');
        $idContribuyente = $this->request->getVar('contribuyente_id');

        $consulta = $sire->query("SELECT
        sire.periodo,sire.anio, sire.contribuyente_id,archivos_sire.id,archivos_sire.constancia_ventas,archivos_sire.constancia_compras,archivos_sire.detalle_preliminar, archivos_sire.ajustes_posteriores, archivos_sire.estado,archivos_sire.sire_id,anio.anio_descripcion,mes.mes_descripcion
        FROM sire
        INNER JOIN archivos_sire ON archivos_sire.sire_id = sire.id
        INNER JOIN anio ON sire.anio = anio.id_anio
        INNER JOIN mes ON mes.id_mes = sire.periodo
        WHERE sire.contribuyente_id = $idContribuyente AND sire.anio = $anio AND sire.periodo = $periodo AND archivos_sire.estado = 1")->getResultArray();

        $rectificar = $this->getPermisosAcciones(42, session()->perfil_id, 'rectificar');
        $detalle = $this->getPermisosAcciones(42, session()->perfil_id, 'ver detalle');
        $eliminar = $this->getPermisosAcciones(42, session()->perfil_id, 'eliminar');

        foreach ($consulta as $key => $value) {
            $acciones = "";

            if ($rectificar) {
                $acciones .= '<button type="button" class="btn btn-warning btn-sm" title="Rectificar Archivos" onclick="rectificar(' . $value['sire_id'] . ', ' . $value['id'] . ', ' . $value['periodo'] . ', ' . $value['anio'] . ', ' . $value['contribuyente_id'] . ', \'' . $value['mes_descripcion'] . '\', ' . $value['anio_descripcion'] . ')">RECT</button> ';
            }

            if ($detalle) {
                $acciones .= '<button type="button" class="btn btn-info btn-sm" title="detalle" onclick="details_archivos(' . $value['sire_id'] . ', \'' . $value['mes_descripcion'] . '\', ' . $value['anio_descripcion'] . ')">DET</button> ';
            }

            if ($eliminar) {
                $acciones .= '<button type="button" class="btn btn-danger btn-sm" title="eliminar" onclick="eliminar(' . $value['sire_id'] . ', ' . $value['id'] . ')"><i class="ti ti-trash"></i></button>';
            }

            $consulta[$key]['acciones'] = $acciones;
        }

        return $this->response->setJSON($consulta);
    }

    public function rectificar()
    {
        $mes = new MesModel();
        $anio_ = new AnioModel();
        $files = new ArchivosSireModel();
        $archivos = new ArchivoTextZipSireModel();
        $contribuyente = new ContribuyenteModel();

        try {
            $files->db->transBegin();

            $idsire = $this->request->getVar('idpdtrenta');
            $idarchivo = $this->request->getVar('idarchivos');
            $periodo = $this->request->getVar('periodoRectificacion');
            $anio = $this->request->getVar('anioRectificacion');
            $idContribuyente = $this->request->getVar('rucRect');

            $file1 = $this->request->getFile('rectConstanciaVentas');
            $file2 = $this->request->getFile('rectConstanciaCompras');
            $file3 = $this->request->getFile('rectDetallePreliminar');
            $file4 = $this->request->getFile('rectAjustePosterior');
            $rectArchivos = $this->request->getFileMultiple('rectArchivos');

            // Verificar que al menos uno de los archivos esté presente
            if ((!$file1 || !$file1->isValid()) && (!$file2 || !$file2->isValid()) && (!$file3 || !$file3->isValid()) && (!$file4 || !$file4->isValid())) {
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

            $archivo_constancia_ventas = "";
            $archivo_constancia_compras = "";
            $archivo_detalle_preliminar = "";
            $archivo_ajuste_posterior = "";

            $dataArchivo = $files->find($idarchivo);

            if ($file1->isValid()) {
                $ext_constancia_ventas = $file1->getExtension();
                $archivo_constancia_ventas = "CONST_VENTAS" . $ruc . "_" . $per . $ani . "_RECT_" . $codigo . "." . $ext_constancia_ventas;
                $file1->move(FCPATH . 'archivos/sire', $archivo_constancia_ventas);
            } else {
                $archivo_constancia_ventas = $dataArchivo['constancia_ventas'];
            }

            if ($file2->isValid()) {
                $ext_constancia_compras = $file2->getExtension();
                $archivo_constancia_compras = "CONST_COMPRAS" . $ruc . "_" . $per . $ani . "_RECT_" . $codigo . "." . $ext_constancia_compras;
                $file2->move(FCPATH . 'archivos/sire', $archivo_constancia_compras);
            } else {
                $archivo_constancia_compras = $dataArchivo['constancia_compras'];
            }

            if ($file3->isValid()) {
                $ext_detalle_preliminar = $file3->getExtension();
                $archivo_detalle_preliminar = "DETALLE_PRELIMINAR" . $ruc . "_" . $per . $ani . "_RECT_" . $codigo . "." . $ext_detalle_preliminar;
                $file3->move(FCPATH . 'archivos/sire', $archivo_detalle_preliminar);
            } else {
                $archivo_detalle_preliminar = $dataArchivo['detalle_preliminar'];
            }

            if ($file4->isValid()) {
                $ext_ajuste_posterior = $file4->getExtension();
                $archivo_ajuste_posterior = "AJUSTE_POSTERIOR" . $ruc . "_" . $per . $ani . "_RECT_" . $codigo . "." . $ext_ajuste_posterior;
                $file4->move(FCPATH . 'archivos/sire', $archivo_ajuste_posterior);
            } else {
                $archivo_ajuste_posterior = $dataArchivo['ajuste_posterior'];
            }

            $datos_files = array(
                "sire_id" => $idsire,
                "constancia_ventas" => $archivo_constancia_ventas,
                "constancia_compras" => $archivo_constancia_compras,
                "detalle_preliminar" => $archivo_detalle_preliminar,
                "ajustes_posteriores" => $archivo_ajuste_posterior,
                "estado" => 1,
                "user_add" => session()->id
            );

            $files->insert($datos_files);

            $files->update($idarchivo, array(
                "estado" => 0,
                "user_edit" => session()->id
            ));

            $archivos->where('sire_id', $idsire)->set([
                "estado" => 0,
                "user_edit" => session()->id
            ])->update();

            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            for ($i = 0; $i < count($rectArchivos); $i++) {

                $name_file = $codigo . "_" . $rectArchivos[$i]->getName();

                $rectArchivos[$i]->move(FCPATH . 'archivos/sire', $name_file);

                $datos_files_txt_zip = array(
                    "sire_id" => $idsire,
                    "name_file" => $name_file,
                    "estado" => 1,
                    "user_add" => session()->id
                );

                $archivos->insert($datos_files_txt_zip);
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

    public function getArchivos($id_sire)
    {
        $files = new ArchivosSireModel();

        $data = $files->where('sire_id', $id_sire)->orderBy('id', 'desc')->findAll();

        return $this->response->setJSON($data);
    }

    public function delete($id_sire, $id_archivo)
    {
        $files = new ArchivosSireModel();
        $sire = new SireModel();
        $archivos = new ArchivoTextZipSireModel();

        try {
            $files->update($id_archivo, array(
                "estado" => 0,
                "user_delete" => session()->id,
                "deleted_at" => date('Y-m-d H:i:s')
            ));

            $sire->update($id_sire, array(
                "estado" => 0,
                "user_delete" => session()->id,
                "deleted_at" => date('Y-m-d H:i:s')
            ));

            $archivos->where('sire_id', $id_sire)->set([
                "estado" => 0,
                "user_delete" => session()->id,
                "deleted_at" => date('Y-m-d H:i:s')
            ])->update();

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

    public function consultaSireRango()
    {
        $sire = new SireModel();

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

        $data = $sire->query("SELECT * from sire inner join mes ON mes.id_mes = sire.periodo inner join archivos_sire ON sire.id = archivos_sire.sire_id where sire.contribuyente_id = '$idcont' and sire.anio = $anio and archivos_sire.estado = 1 and sire.periodo BETWEEN '$desde' and '$hasta'")->getResult();

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Consulta correctamente",
            "data" => $data
        ]);
    }

    public function files($id_sire)
    {
        $files = new ArchivoTextZipSireModel();

        $data = $files->where('sire_id', $id_sire)->where('estado', 1)->orderBy('id', 'desc')->findAll();

        return $this->response->setJSON($data);
    }

    public function deleteFile($id)
    {
        $files = new ArchivoTextZipSireModel();

        $files->update($id, array(
            "estado" => 0,
            "user_delete" => session()->id,
            "deleted_at" => date('Y-m-d H:i:s')
        ));

        return $this->response->setJSON([
            "status" => "success",
            "message" => "Se elimino correctamente"
        ]);
    }

    public function notificacionSire()
    {
        $sire = new SireModel();
        $contrib = new ContribuyenteModel();
        $fecha_declaracion = new FechaDeclaracionModel();

        $hoy = date('Y-m-d');

        //consulta de las notificaciones
        $declaracion = $fecha_declaracion->query("SELECT fd.id_anio, fd.id_mes, fd.id_numero, fd.fecha_exacta, fd.fecha_notificar, a.anio_descripcion, m.mes_descripcion FROM `fecha_declaracion` AS fd INNER JOIN anio as a ON a.id_anio = fd.id_anio INNER JOIN mes as m ON m.id_mes = fd.id_mes WHERE fd.id_tributo = 27 and fd.id_anio >= 11 and fd.fecha_exacta is not null and fd.fecha_notificar <= '$hoy'")->getResultArray();

        $data_declarar = [];

        foreach ($declaracion as $key => $value) {
            $digito = $value['id_numero'] - 1;

            $listaContrib = $contrib->query("SELECT id, razon_social, ruc FROM contribuyentes WHERE estado = 1 AND tipoServicio = 'CONTABLE' AND RIGHT(ruc, 1) = $digito")->getResultArray();

            foreach ($listaContrib as $keys => $values) {
                $id = $values['id'];

                $querySire = $sire->where('contribuyente_id', $id)->where('periodo', $value['id_mes'])->where('anio', $value['id_anio'])->where('estado', 1)->first();

                if (!$querySire) {
                    $insert = [
                        "contribuyente_id" => $id,
                        "contribuyente" => $values['razon_social'],
                        "anio" => $value['anio_descripcion'],
                        "mes" => $value['mes_descripcion']
                    ];

                    array_push($data_declarar, $insert);
                }
            }
        }

        return $this->response->setJSON($data_declarar);
    }
}
