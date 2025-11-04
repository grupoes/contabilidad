<?php

namespace App\Controllers;

use App\Models\AnioModel;
use App\Models\PdtAnualModel;
use App\Models\ArchivosPdtAnualModel;
use App\Models\TributoModel;
use App\Models\ContribuyenteModel;
use App\Models\PdtModel;
use App\Models\PagoAnualModel;

class PdtAnual extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $anio = new AnioModel();

        $anios = $anio->query("SELECT * FROM anio WHERE anio_estado = 1 AND anio_descripcion <= YEAR(CURDATE()) ORDER BY anio_descripcion DESC")->getResult();

        $menu = $this->permisos_menu();

        return view('declaraciones/pdtanual', compact('anios', 'menu'));
    }

    public function verificar($ruc)
    {
        $tributo = new TributoModel();
        $cont = new ContribuyenteModel();

        $consulta = $tributo->query("SELECT tributo.tri_descripcion,pdt.pdt_descripcion,configuracion_notificacion.ruc_empresa_numero,configuracion_notificacion.id_tributo,tributo.id_pdt
        FROM tributo
        INNER JOIN configuracion_notificacion ON configuracion_notificacion.id_tributo = tributo.id_tributo
        INNER JOIN pdt ON tributo.id_pdt = pdt.id_pdt
        WHERE configuracion_notificacion.ruc_empresa_numero = $ruc and (tributo.id_pdt = 3 or tributo.id_pdt = 4 or tributo.id_pdt = 5 or tributo.id_pdt = 6)")->getResult();

        $data = $cont->where('ruc', $ruc)->first();

        $anual = $data['costoAnual'];

        $datos = array(
            "tipo_pdt" => $consulta,
            "montoAnual" => $anual
        );

        return $this->response->setJSON($datos);
    }


    public function consulta()
    {
        $pdtAnual = new PdtAnualModel();

        $desde = $this->request->getVar("desde");
        $hasta = $this->request->getVar("hasta");
        $ruc = $this->request->getVar('rucNum');

        if ($desde > $hasta) {
            $data = [
                "status" => "error",
                "message" => "La fecha de Inicio (desde) no puede ser mayor a la fecha final (hasta)"
            ];

            return $this->response->setJSON($data);
        }

        $consulta = $pdtAnual->query("SELECT * from pdt_anual inner join anio ON anio.id_anio = pdt_anual.periodo inner join archivos_pdtanual ON pdt_anual.id_pdt_anual = archivos_pdtanual.id_pdt_anual where pdt_anual.ruc_empresa = '$ruc' and archivos_pdtanual.estado = 1 and pdt_anual.periodo BETWEEN $desde and $hasta")->getResult();

        $data = [
            "status" => "success",
            "data" => $consulta
        ];

        return $this->response->setJSON($data);
    }

    public function getBalance()
    {
        $pdtAnual = new PdtAnualModel();

        $anio = $this->request->getVar('anio');
        $ruc = $this->request->getVar('ruc');
        $tipoPdt = $this->request->getVar('pdt');

        $sql = "AND pdt_anual.periodo = $anio";

        if ($tipoPdt != "0") {
            $sql = "AND pdt_anual.periodo = $anio AND pdt_anual.id_pdt_tipo = $tipoPdt";
        }

        $consulta = $pdtAnual->query("SELECT pdt.pdt_descripcion,pdt_anual.ruc_empresa,pdt_anual.periodo,pdt_anual.id_pdt_tipo,archivos_pdtanual.id_pdt_anual,archivos_pdtanual.id_archivo_anual,archivos_pdtanual.pdt,archivos_pdtanual.constancia,anio.anio_descripcion
        FROM pdt_anual
        INNER JOIN pdt ON pdt_anual.id_pdt_tipo = pdt.id_pdt
        INNER JOIN archivos_pdtanual ON pdt_anual.id_pdt_anual = archivos_pdtanual.id_pdt_anual
        INNER JOIN anio ON pdt_anual.periodo = anio.id_anio
        WHERE pdt_anual.ruc_empresa = $ruc AND archivos_pdtanual.estado = 1 $sql")->getResult();

        return $this->response->setJSON($consulta);
    }

    public function guardar()
    {
        $pdtAnual = new PdtAnualModel();
        $archivosPdtAnual = new ArchivosPdtAnualModel();
        $pdt = new PdtModel();
        $anio = new AnioModel();
        $contr = new ContribuyenteModel();
        $pagoAnual = new PagoAnualModel();

        try {
            $pdt->db->transBegin();

            $anio_post = $this->request->getVar('anio');
            $typePdt = $this->request->getVar('typePdt');
            $ruc = $this->request->getVar('idruc');

            // Verificar si ya existe registro
            $consulta = $pdtAnual->where("ruc_empresa", $ruc)
                ->where("id_pdt_tipo", $typePdt)
                ->where("periodo", $anio_post)
                ->where("estado", 1)
                ->first();

            if ($consulta) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Ya existe un registro para el año y tipo de PDT seleccionado"
                ]);
            }

            // Validar archivos
            $pdt_file = $this->request->getFile('pdt');
            $constancia = $this->request->getFile('constancia');

            if (!$pdt_file || !$constancia) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'No se recibieron todos los archivos requeridos'
                ]);
            }

            if (!$pdt_file->isValid() || !$constancia->isValid()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Uno o ambos archivos no son válidos'
                ]);
            }

            if ($pdt_file->getClientMimeType() !== 'application/pdf' || $constancia->getClientMimeType() !== 'application/pdf') {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Solo se permiten archivos PDF'
                ]);
            }

            // Obtener datos del contribuyente
            $data_contribuyente = $contr->where('ruc', $ruc)->first();
            $razon_social = $data_contribuyente['razon_social'] ?? '';

            // Procesar cargo
            $isCargo = 0;
            $monto = 0;
            $descripcion = "";
            $estado_envio = "";
            $respuestaFactura = null;

            if ($this->request->getPost('cargo') !== null) {
                $isCargo = 1;
                $monto = $this->request->getVar('monto') ?? 0;
                $descripcion = $this->request->getVar('descripcion') ?? "";

                if ($monto <= 0) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'El monto debe ser mayor a 0'
                    ]);
                }

                $estado_envio = "Pendiente";
            }

            // Guardar datos principales
            $data_pdt_anual = [
                "ruc_empresa" => $ruc,
                "periodo" => $anio_post,
                "id_pdt_tipo" => $typePdt,
                "cargo" => $isCargo,
                "monto" => $monto,
                "razon_social" => $razon_social,
                "descripcion" => $descripcion,
                "user_add" => session()->id,
                "estado_envio" => $estado_envio,
                "estado" => 1
            ];

            $pdtAnual->insert($data_pdt_anual);
            $id = $pdtAnual->getInsertID();

            // Generar nombres de archivos y mover
            $dataPdt = $pdt->select("pdt_descripcion")->find($typePdt);
            $nombre_pdt = $dataPdt['pdt_descripcion'];

            $dataAnio = $anio->select("anio_descripcion")->find($anio_post);
            $anio_descripcion = $dataAnio['anio_descripcion'];

            $pdt_anual = "PDT_" . $ruc . "_" . $nombre_pdt . "_" . $anio_descripcion . ".pdf";
            $constancia_anual = "CONSTANCIA_" . $ruc . "_" . $nombre_pdt . "_" . $anio_descripcion . ".pdf";

            $pdt_file->move(FCPATH . 'archivos/pdt', $pdt_anual);
            $constancia->move(FCPATH . 'archivos/pdt', $constancia_anual);

            // Guardar archivos
            $data_archivos_pdt_anual = [
                "id_pdt_anual" => $id,
                "pdt" => $pdt_anual,
                "constancia" => $constancia_anual,
                "monto" => $monto,
                "descripcion" => $descripcion,
                "user_add" => session()->id,
                "estado" => 1
            ];

            $archivosPdtAnual->insert($data_archivos_pdt_anual);

            if ($typePdt == 3) {
                $data_pago_anual = [
                    "pdt_anual_id" => $id,
                    "contribuyente_id" => $data_contribuyente['id'],
                    "monto_total" => $monto,
                    "anio_correspondiente" => $anio_descripcion,
                    "monto_pagado" => 0.00,
                    "monto_pendiente" => $monto,
                    "usuario_id_cobra" => session()->id,
                    "estado" => "Pendiente"
                ];

                $pagoAnual->insert($data_pago_anual);
            }

            // Verificar transacción y hacer commit
            if ($pdt->db->transStatus() === false) {
                $pdt->db->transRollback();
                throw new \Exception("Error al realizar la operación en la base de datos");
            }

            $pdt->db->transCommit();

            // Preparar respuesta
            $responseData = [
                "status" => "success",
                "message" => "Registro guardado correctamente"
            ];

            return $this->response->setJSON($responseData);
        } catch (\Exception $e) {
            $pdt->db->transRollback();
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function rectificar()
    {
        $archivosPdtAnual = new ArchivosPdtAnualModel();
        $anio = new AnioModel();
        $pdt = new PdtModel();

        try {
            $data = $this->request->getPost();

            $idpdt = $data['idpdt'];
            $idpdttipo = $data['idpdttipo'];
            $idanio = $data['idanio'];
            $idArchivoAnual = $data['idArchivoAnual'];
            $ruc = $data['rucNumber'];

            $file1 = $this->request->getFile('pdt_rectificar');
            $file2 = $this->request->getFile('constancia_rectificar');

            // Verificar que al menos uno de los archivos esté presente
            if ((!$file1 || !$file1->isValid()) && (!$file2 || !$file2->isValid())) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Debe seleccionar al menos un archivo"
                ]);
            }

            $dataPdt = $pdt->select("pdt_descripcion")->find($idpdttipo);
            $nombre_pdt = $dataPdt['pdt_descripcion'];

            $data_anio = $anio->find($idanio);
            $ani = $data_anio['anio_descripcion'];

            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            $archivo_pdt = "";
            $archivo_constancia = "";

            $dataArchivo = $archivosPdtAnual->find($idArchivoAnual);

            if ($file1->isValid()) {
                $archivo_pdt = "PDT_" . $ruc . "_" . $nombre_pdt . "_" . $ani . "_RECT_" . $codigo . ".pdf";
                $file1->move(FCPATH . 'archivos/pdt', $archivo_pdt);
            } else {
                $archivo_pdt = $dataArchivo['pdt'];
            }

            if ($file2->isValid()) {
                $archivo_constancia = "CONSTANCIA_" . $ruc . "_" . $nombre_pdt . "_" . $ani . "_RECT_" . $codigo . ".pdf";
                $file2->move(FCPATH . 'archivos/pdt', $archivo_constancia);
            } else {
                $archivo_constancia = $dataArchivo['constancia'];
            }

            $archivosPdtAnual->set('estado', 0);
            $archivosPdtAnual->where('id_archivo_anual', $idArchivoAnual);
            $archivosPdtAnual->update();

            $datos_files = array(
                "id_pdt_anual" => $idpdt,
                "pdt" => $archivo_pdt,
                "constancia" => $archivo_constancia,
                "estado" => 1,
                "user_id" => session()->id
            );

            $archivosPdtAnual->insert($datos_files);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Archivos rectificados correctamente"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deleteAnual($idArchivo, $idPdtAnual)
    {
        $archivosPdtAnual = new ArchivosPdtAnualModel();
        $pdtAnual = new PdtAnualModel();

        try {
            $archivosPdtAnual->update($idArchivo, [
                'estado' => 0,
                'user_delete' => session()->id
            ]);

            $pdtAnual->update($idPdtAnual, [
                'estado' => 0,
                'user_delete' => session()->id
            ]);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Registro eliminado correctamente"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
