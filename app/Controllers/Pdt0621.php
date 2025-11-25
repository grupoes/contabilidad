<?php

namespace App\Controllers;

use App\Models\AnioModel;
use App\Models\MesModel;
use App\Models\PdtRentaModel;
use App\Models\ArchivosPdt0621Model;
use App\Models\ContribuyenteModel;

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

        $menu = $this->permisos_menu();

        return view('declaraciones/pdt0621', compact('anios', 'meses', 'menu'));
    }

    public function filesSave()
    {
        $pdtRenta = new PdtRentaModel();
        $mes = new MesModel();
        $anio_ = new AnioModel();
        $files = new ArchivosPdt0621Model();

        try {
            $files->db->transBegin();

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

            $consultaRenta = $pdtRenta->where('ruc_empresa', $ruc)->where('periodo', $periodo)->where('anio', $anio)->where('estado', 1)->first();

            if ($consultaRenta) {
                return $this->response->setJSON(['error' => 'success', 'message' => "El periodo y año ya existe."]);
            }

            $data_periodo = $mes->find($periodo);

            $data_anio = $anio_->find($anio);

            $per = strtoupper($data_periodo['mes_descripcion']);
            $ani = $data_anio['anio_descripcion'];

            $ext_renta = $file_renta->getExtension();
            $ext_constancia = $file_constancia->getExtension();

            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            $archivo_pdt = "PDT0621_" . $ruc . "_" . $per . $ani . "_" . $codigo . "." . $ext_renta;
            $archivo_constancia = "CONST_" . $ruc . "_" . $per . $ani . "." . $ext_constancia;

            $file_renta->move(FCPATH . 'archivos/pdt', $archivo_pdt);
            $file_constancia->move(FCPATH . 'archivos/pdt', $archivo_constancia);

            $modo = getenv("MODO");

            if ($modo == "PRODUCCION") {
                $dockerPath = FCPATH; // /var/www/html/public/
                $realPath = str_replace('/var/www/html', '/var/www/html/contabilidad', $dockerPath);

                $rutaPdt = $realPath . 'archivos/pdt/' . $archivo_pdt;
                $rutaConstancia = $realPath . 'archivos/pdt/' . $archivo_constancia;
            } else {
                $rutaPdt = FCPATH . '/archivos/pdt/' . $archivo_pdt;
                $rutaConstancia = FCPATH . '/archivos/pdt/' . $archivo_constancia;
            }

            //aqui verificar si coinciden los archivos correspondientes
            $datos_pdt_file = $this->apiLoadPdtArchivos($rutaPdt);

            if ($datos_pdt_file['texto'] !== "") {
                if ($datos_pdt_file['success'] == false || $datos_pdt_file['texto_encontrado'] === false) {
                    unlink($rutaPdt);
                    unlink($rutaConstancia);
                    return $this->response->setJSON(['status' => 'error', 'message' => "El archivo de renta no es correcto.", 'datos' => $datos_pdt_file]);
                }

                if ($datos_pdt_file['ruc'] != $ruc) {
                    unlink($rutaPdt);
                    unlink($rutaConstancia);
                    return $this->response->setJSON(['status' => 'error', 'message' => "El archivo de renta no es correcto, RUC no coincide.", 'datos' => $datos_pdt_file]);
                }

                if ($datos_pdt_file['periodo'] != $ani . $data_periodo['mes_fecha']) {
                    unlink($rutaPdt);
                    unlink($rutaConstancia);
                    return $this->response->setJSON(['status' => 'error', 'message' => "El archivo de renta no es correcto, periodo no coincide.", 'datos' => $datos_pdt_file]);
                }
            }

            $datos_constancia_file = $this->apiLoadPdtArchivos($rutaConstancia);

            if ($datos_constancia_file['texto'] !== "") {
                if ($datos_constancia_file['success'] == 0 || $datos_constancia_file['texto_encontrado'] === false) {
                    unlink($rutaPdt);
                    unlink($rutaConstancia);
                    return $this->response->setJSON(['status' => 'error', 'message' => "El archivo de constancia no es correcto.", 'datos' => $datos_constancia_file]);
                }

                if ($datos_constancia_file['periodo'] != $ani . $data_periodo['mes_fecha'] || $datos_constancia_file['ruc'] != $ruc) {
                    unlink($rutaPdt);
                    unlink($rutaConstancia);
                    return $this->response->setJSON(['status' => 'error', 'message' => "El archivo de constancia no es correcto, periodo o RUC no coinciden.", 'datos' => $datos_constancia_file]);
                }
            }

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

            $totalVentas = 0;
            $totalCompras = 0;

            $rutaPdtMontos = FCPATH . '/archivos/pdt/' . $archivo_pdt;

            $datos = $this->apiLoadPdtFile($rutaPdtMontos);

            if ($datos_pdt_file['texto'] !== "") {
                if ($datos['status'] === 'success') {
                    $compras = $datos['igv_compras'];
                    $ventas = $datos['igv_ventas'];

                    $totalVentas = $ventas['100'] + $ventas['154'] - $ventas['102'] + $ventas['160'] - $ventas['162'] + $ventas['106'] + $ventas['127'] + $ventas['105'] + $ventas['109'] + $ventas['112'];

                    $totalCompras = $compras['107'] + $compras['156'] + $compras['110'] + $compras['113'] + $compras['114'] + $compras['116'] + $compras['119'] + $compras['120'] + $compras['122'];

                    $descuentos = $ventas['102'] + $ventas['162'];

                    if ($ventas['100'] >= $descuentos) {
                        $venta_gravada = $ventas['100'] - $descuentos;
                        $venta_no_gravada = $totalVentas - $venta_gravada + $descuentos;
                    } else {
                        $venta_gravada = $ventas['100'];
                        $venta_no_gravada = $totalVentas - $venta_gravada;
                    }

                    $data_update = array(
                        "total_ventas" => $totalVentas,
                        "total_compras" => $totalCompras,
                        "compras_gravadas" => $datos['compra_gravada'],
                        "compras_no_gravadas" => $datos['compra_no_gravada'],
                        "ventas_gravadas" => $venta_gravada,
                        "ventas_no_gravadas" => $venta_no_gravada,
                        "renta_pdt" => $datos['renta_pdt']
                    );

                    $pdtRenta->update($pdtRentaId, $data_update);
                }
            }

            $rutaLink = 'archivos/pdt/' . $archivo_pdt;

            if ($files->db->transStatus() === false) {
                $files->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $files->db->transCommit();

            return $this->response->setJSON(['status' => 'success', 'message' => "Se registro correctamente", 'texto' => "", "ruta" => $rutaLink, "idpdt" => $pdtRentaId]);
        } catch (\Exception $e) {
            $files->db->transRollback();
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
        pdt_renta.periodo,pdt_renta.anio, pdt_renta.ruc_empresa,archivos_pdt0621.id_archivos_pdt,archivos_pdt0621.nombre_pdt,archivos_pdt0621.nombre_constancia,archivos_pdt0621.estado,archivos_pdt0621.id_pdt_renta,anio.anio_descripcion,mes.mes_descripcion
        FROM pdt_renta
        INNER JOIN archivos_pdt0621 ON archivos_pdt0621.id_pdt_renta = pdt_renta.id_pdt_renta
        INNER JOIN anio ON pdt_renta.anio = anio.id_anio
        INNER JOIN mes ON mes.id_mes = pdt_renta.periodo
        WHERE pdt_renta.ruc_empresa = $ruc AND pdt_renta.anio = $anio AND pdt_renta.periodo = $periodo AND archivos_pdt0621.estado = 1")->getResultArray();

        $rectificar = $this->getPermisosAcciones(8, session()->perfil_id, 'rectificar');
        $detalle = $this->getPermisosAcciones(8, session()->perfil_id, 'ver detalle');
        $eliminar = $this->getPermisosAcciones(8, session()->perfil_id, 'eliminar');

        foreach ($consulta as $key => $value) {
            $acciones = "";

            if ($rectificar) {
                $acciones .= '<button type="button" class="btn btn-warning btn-sm" title="Rectificar Archivos" onclick="rectificar(' . $value['id_pdt_renta'] . ', ' . $value['id_archivos_pdt'] . ', ' . $value['periodo'] . ', ' . $value['anio'] . ', ' . $value['ruc_empresa'] . ', \'' . $value['mes_descripcion'] . '\', ' . $value['anio_descripcion'] . ')">RECT</button> ';
            }

            if ($detalle) {
                $acciones .= '<button type="button" class="btn btn-info btn-sm" title="detalle" onclick="details_archivos(' . $value['id_pdt_renta'] . ', \'' . $value['mes_descripcion'] . '\', ' . $value['anio_descripcion'] . ')">DET</button> ';
            }

            if ($eliminar) {
                $acciones .= '<button type="button" class="btn btn-danger btn-sm" title="eliminar" onclick="eliminar(' . $value['id_pdt_renta'] . ', ' . $value['id_archivos_pdt'] . ')"><i class="ti ti-trash"></i></button>';
            }

            $consulta[$key]['acciones'] = $acciones;
        }

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

    public function pdtRectificacion()
    {
        $mes = new MesModel();
        $anio_ = new AnioModel();
        $files = new ArchivosPdt0621Model();
        $pdtRenta = new PdtRentaModel();

        try {
            $files->db->transBegin();

            $idpdt = $this->request->getVar('idpdtrenta');
            $idarchivo = $this->request->getVar('idarchivos');
            $periodo = $this->request->getVar('periodoRectificacion');
            $anio = $this->request->getVar('anioRectificacion');
            $ruc = $this->request->getVar('rucRect');

            $file1 = $this->request->getFile('filePdt');
            $file2 = $this->request->getFile('fileConstancia');

            // Verificar que al menos uno de los archivos esté presente
            if ((!$file1 || !$file1->isValid()) && (!$file2 || !$file2->isValid())) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Debe seleccionar al menos un archivo"
                ]);
            }

            $data_periodo = $mes->find($periodo);

            $data_anio = $anio_->find($anio);

            $per = strtoupper($data_periodo['mes_descripcion']);
            $ani = $data_anio['anio_descripcion'];

            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            $archivo_pdt = "";
            $archivo_constancia = "";

            $dataArchivo = $files->find($idarchivo);

            $modo = getenv("MODO");

            $m = "1";

            if ($file1->isValid()) {
                $ext_pdt = $file1->getExtension();
                $archivo_pdt = "PDT0621_" . $ruc . "_" . $per . $ani . "_RECT_" . $codigo . "." . $ext_pdt;
                $file1->move(FCPATH . 'archivos/pdt', $archivo_pdt);

                if ($modo == "PRODUCCION") {
                    $dockerPath = FCPATH; // /var/www/html/public/
                    $realPath = str_replace('/var/www/html', '/var/www/html/contabilidad', $dockerPath);

                    $rutaPdt = $realPath . 'archivos/pdt/' . $archivo_pdt;
                } else {
                    $rutaPdt = FCPATH . 'archivos/pdt/' . $archivo_pdt;
                }

                $datos_pdt_file = $this->apiLoadPdtArchivos($rutaPdt);

                if ($datos_pdt_file['texto'] !== "") {
                    if ($datos_pdt_file['success'] == false || $datos_pdt_file['texto_encontrado'] === false) {
                        unlink($rutaPdt);
                        return $this->response->setJSON(['status' => 'error', 'message' => "El archivo de renta no es correcto.", 'datos' => $datos_pdt_file]);
                    }

                    if ($datos_pdt_file['ruc'] != $ruc) {
                        unlink($rutaPdt);
                        return $this->response->setJSON(['status' => 'error', 'message' => "El archivo de renta no es correcto, RUC no coincide.", 'datos' => $datos_pdt_file]);
                    }

                    if ($datos_pdt_file['periodo'] != $ani . $data_periodo['mes_fecha']) {
                        unlink($rutaPdt);
                        return $this->response->setJSON(['status' => 'error', 'message' => "El archivo de renta no es correcto, periodo no coincide.", 'datos' => $datos_pdt_file]);
                    }

                    $totalVentas = 0;
                    $totalCompras = 0;

                    $rutaPdtMontos = FCPATH . '/archivos/pdt/' . $archivo_pdt;

                    $datos = $this->apiLoadPdtFile($rutaPdtMontos);

                    if ($datos['status'] === 'success') {
                        $compras = $datos['igv_compras'];
                        $ventas = $datos['igv_ventas'];

                        $totalVentas = $ventas['100'] + $ventas['154'] - $ventas['102'] + $ventas['160'] - $ventas['162'] + $ventas['106'] + $ventas['127'] + $ventas['105'] + $ventas['109'] + $ventas['112'];

                        $totalCompras = $compras['107'] + $compras['156'] + $compras['110'] + $compras['113'] + $compras['114'] + $compras['116'] + $compras['119'] + $compras['120'] + $compras['122'];

                        $data_update = array(
                            "total_ventas" => $totalVentas,
                            "total_compras" => $totalCompras,
                            "compras_gravadas" => $datos['compra_gravada'],
                            "compras_no_gravadas" => $datos['compra_no_gravada'],
                            "ventas_gravadas" => $datos['venta_gravada'],
                            "ventas_no_gravadas" => $datos['venta_no_gravada'],
                            "renta_pdt" => $datos['renta_pdt']
                        );

                        $pdtRenta->update($dataArchivo['id_pdt_renta'], $data_update);
                    }
                } else {
                    $m = "";
                }
            } else {
                $archivo_pdt = $dataArchivo['nombre_pdt'];
            }

            if ($file2->isValid()) {
                $ext_constancia = $file2->getExtension();
                $archivo_constancia = "CONST_" . $ruc . "_" . $per . $ani . "_RECT_" . $codigo . "." . $ext_constancia;
                $file2->move(FCPATH . 'archivos/pdt', $archivo_constancia);

                if ($modo == "PRODUCCION") {
                    $dockerPath = FCPATH; // /var/www/html/public/
                    $realPath = str_replace('/var/www/html', '/var/www/html/contabilidad', $dockerPath);

                    $rutaConstancia = $realPath . 'archivos/pdt/' . $archivo_constancia;
                } else {
                    $rutaConstancia = FCPATH . 'archivos/pdt/' . $archivo_constancia;
                }

                $datos_constancia_file = $this->apiLoadPdtArchivos($rutaConstancia);

                if ($datos_constancia_file['texto'] !== "") {
                    if ($datos_constancia_file['success'] == 0 || $datos_constancia_file['texto_encontrado'] === false) {
                        unlink($rutaConstancia);
                        return $this->response->setJSON(['status' => 'error', 'message' => "El archivo de constancia no es correcto.", 'datos' => $datos_constancia_file]);
                    }

                    if ($datos_constancia_file['periodo'] != $ani . $data_periodo['mes_fecha'] || $datos_constancia_file['ruc'] != $ruc) {
                        unlink($rutaConstancia);
                        return $this->response->setJSON(['status' => 'error', 'message' => "El archivo de constancia no es correcto, periodo o RUC no coinciden.", 'datos' => $datos_constancia_file]);
                    }
                }
            } else {
                $archivo_constancia = $dataArchivo['nombre_constancia'];
            }

            $datos_files = array(
                "id_pdt_renta" => $idpdt,
                "nombre_pdt" => $archivo_pdt,
                "nombre_constancia" => $archivo_constancia,
                "estado" => 1,
                "user_id" => session()->id
            );

            $files->insert($datos_files);

            $files->update($idarchivo, array(
                "estado" => 0
            ));

            if ($files->db->transStatus() === false) {
                $files->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $files->db->transCommit();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se registro correctamente",
                "texto" => $m,
                "ruta" => "archivos/pdt/" . $archivo_pdt,
                "idpdt" => $idpdt
            ]);
        } catch (\Exception $e) {
            $files->db->transRollback();
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrio un error " . $e->getMessage()
            ]);
        }
    }

    public function getArchivos($id_pdt_renta)
    {
        $files = new ArchivosPdt0621Model();

        $data = $files->where('id_pdt_renta', $id_pdt_renta)->orderBy('id_archivos_pdt', 'desc')->findAll();

        return $this->response->setJSON($data);
    }

    public function transacciones()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $anio = new AnioModel();
        $mes = new MesModel();

        $anios = $anio->query("SELECT * FROM anio WHERE anio_estado = 1 AND anio_descripcion BETWEEN '2025' AND YEAR(CURDATE()) ORDER BY anio_descripcion DESC")->getResult();

        $menu = $this->permisos_menu();

        return view('declaraciones/pdt_renta_transacciones', compact('anios', 'menu'));
    }

    public function listEmpresas()
    {
        $contri = new ContribuyenteModel();
        $data = $this->request->getPost();

        $anio = $data['anio'];
        $search = $data['search'];
        $filter = $data['filter'];
        $estado = $data['estado'];

        switch ($filter) {
            case 1:
                $order = "ORDER BY (SUM(pr.total_compras) + SUM(pr.total_ventas)) DESC";
                break;
            case 2:
                $order = "ORDER BY (SUM(pr.total_compras) + SUM(pr.total_ventas)) ASC";
                break;
            case 3:
                $order = "ORDER BY SUM(pr.total_compras) DESC";
                break;
            case 4:
                $order = "ORDER BY SUM(pr.total_compras) ASC";
                break;
            case 5:
                $order = "ORDER BY SUM(pr.total_ventas) DESC";
                break;
            case 6:
                $order = "ORDER BY SUM(pr.total_ventas) ASC";
                break;
            default:
                $order = "";
                break;
        }

        $data = $contri->query("SELECT 
        c.ruc,
        c.razon_social,
        -- Contar solo los que tienen 0.00 y excluido = 'NO'
        SUM(CASE WHEN pr.total_compras = 0.00 AND pr.excluido = 'NO' THEN 1 ELSE 0 END) AS compras_en_cero,
        SUM(CASE WHEN pr.total_ventas = 0.00 AND pr.excluido = 'NO' THEN 1 ELSE 0 END) AS ventas_en_cero,
        -- Totales generales (sin importar excluido)
        FORMAT(IFNULL(SUM(pr.total_compras), 0), 2) AS total_compras_decimal,
        FORMAT(IFNULL(SUM(pr.total_ventas), 0), 2) AS total_ventas_decimal,
        IFNULL(SUM(pr.total_compras), 0) AS total_compras,
        IFNULL(SUM(pr.total_ventas), 0) AS total_ventas
        FROM contribuyentes c
        INNER JOIN configuracion_notificacion cn 
        ON cn.ruc_empresa_numero = c.ruc
        LEFT JOIN pdt_renta pr 
        ON pr.ruc_empresa = c.ruc 
        AND pr.anio = $anio
        AND pr.estado = 1

        WHERE 
        cn.id_tributo = 2
        AND c.estado = $estado 
        AND (c.razon_social LIKE '%$search%' OR c.ruc like '%$search%')

        GROUP BY 
        c.ruc, c.razon_social
        $order;")->getResultArray();

        return $this->response->setJSON($data);
    }

    public function listaPeriodos($ruc, $anio)
    {
        $pdt = new PdtRentaModel();

        $data = $pdt->query("SELECT pr.id_pdt_renta, pr.periodo, pr.anio, FORMAT(pr.total_compras, 2, 'es_PE') as total_compras_decimal, FORMAT(pr.total_ventas, 2, 'es_PE') as total_ventas_decimal, pr.total_compras, pr.total_ventas, c.razon_social, pr.ruc_empresa, m.mes_descripcion, a.anio_descripcion, ap.nombre_pdt FROM pdt_renta pr INNER JOIN contribuyentes c ON c.ruc = pr.ruc_empresa INNER JOIN mes m ON m.id_mes = pr.periodo INNER JOIN anio a ON a.id_anio = pr.anio INNER JOIN archivos_pdt0621 ap ON ap.id_pdt_renta = pr.id_pdt_renta WHERE pr.ruc_empresa = '$ruc' AND pr.anio = '$anio' AND pr.estado = 1 AND ap.estado = 1 ORDER BY pr.periodo asc")->getResultArray();

        return $this->response->setJSON($data);
    }

    public function listaPeriodosMypes($ruc, $anio)
    {
        $pdt = new PdtRentaModel();

        $data = $pdt->query("SELECT pr.id_pdt_renta, pr.periodo, pr.anio, FORMAT(pr.total_compras, 2, 'es_PE') as total_compras_decimal, FORMAT(pr.total_ventas, 2, 'es_PE') as total_ventas_decimal, FORMAT(pr.compras_gravadas, 2, 'es_PE') as compras_gravadas_decimal, FORMAT(pr.compras_no_gravadas, 2, 'es_PE') as compras_no_gravadas_decimal, FORMAT(pr.ventas_gravadas, 2, 'es_PE') as ventas_gravadas_decimal, FORMAT(pr.ventas_no_gravadas, 2, 'es_PE') as ventas_no_gravadas_decimal, pr.total_compras, pr.total_ventas, c.razon_social, pr.ruc_empresa, m.mes_descripcion, a.anio_descripcion, ap.nombre_pdt FROM pdt_renta pr INNER JOIN contribuyentes c ON c.ruc = pr.ruc_empresa INNER JOIN mes m ON m.id_mes = pr.periodo INNER JOIN anio a ON a.id_anio = pr.anio INNER JOIN archivos_pdt0621 ap ON ap.id_pdt_renta = pr.id_pdt_renta WHERE pr.ruc_empresa = '$ruc' AND pr.anio = '$anio' AND pr.estado = 1 AND ap.estado = 1 ORDER BY pr.periodo asc")->getResultArray();

        return $this->response->setJSON($data);
    }

    public function updateMontos()
    {
        $pdt = new PdtRentaModel();

        try {
            $data = $this->request->getPost();

            $id = $data['idPdt'];
            $total_ventas = $data['monto_venta'];
            $total_compras = $data['monto_compra'];

            $data_update = array(
                "total_ventas" => $total_ventas,
                "total_compras" => $total_compras
            );

            $pdt->update($id, $data_update);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se actualizo correctamente"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrio un error " . $e->getMessage()
            ]);
        }
    }

    public function delete($id_pdt_renta, $id_archivos_pdt)
    {
        $files = new ArchivosPdt0621Model();
        $pdtRenta = new PdtRentaModel();

        try {
            $files->update($id_archivos_pdt, array(
                "estado" => 0,
                "user_delete" => session()->id,
            ));

            $pdtRenta->update($id_pdt_renta, array(
                "estado" => 0,
                "user_delete" => session()->id,
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

    public function mypes()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $anio = new AnioModel();
        $mes = new MesModel();

        $anios = $anio->query("SELECT * FROM anio WHERE anio_estado = 1 AND anio_descripcion BETWEEN '2025' AND YEAR(CURDATE()) ORDER BY anio_descripcion DESC")->getResult();

        $menu = $this->permisos_menu();

        return view('declaraciones/pdt_renta_mypes', compact('anios', 'menu'));
    }

    public function listEmpresasMypes()
    {
        $contri = new ContribuyenteModel();
        $data = $this->request->getPost();

        $anio = $data['anio'];
        $search = $data['search'];
        $filter = $data['filter'];
        $estado = $data['estado'];

        switch ($filter) {
            case 1:
                $order = "ORDER BY (SUM(pr.total_compras) + SUM(pr.total_ventas)) DESC";
                break;
            case 2:
                $order = "ORDER BY (SUM(pr.total_compras) + SUM(pr.total_ventas)) ASC";
                break;
            case 3:
                $order = "ORDER BY SUM(pr.total_compras) DESC";
                break;
            case 4:
                $order = "ORDER BY SUM(pr.total_compras) ASC";
                break;
            case 5:
                $order = "ORDER BY SUM(pr.total_ventas) DESC";
                break;
            case 6:
                $order = "ORDER BY SUM(pr.total_ventas) ASC";
                break;
            default:
                $order = "";
                break;
        }

        $data = $contri->query("SELECT 
        c.ruc,
        c.razon_social,
        -- Contar solo los que tienen 0.00 y excluido = 'NO'
        SUM(CASE WHEN pr.total_compras = 0.00 AND pr.excluido = 'NO' THEN 1 ELSE 0 END) AS compras_en_cero,
        SUM(CASE WHEN pr.total_ventas = 0.00 AND pr.excluido = 'NO' THEN 1 ELSE 0 END) AS ventas_en_cero,
        -- Totales generales (sin importar excluido)
        FORMAT(IFNULL(SUM(pr.total_compras), 0), 2) AS total_compras_decimal,
        FORMAT(IFNULL(SUM(pr.total_ventas), 0), 2) AS total_ventas_decimal,
        IFNULL(SUM(pr.total_compras), 0) AS total_compras,
        IFNULL(SUM(pr.total_ventas), 0) AS total_ventas
        FROM contribuyentes c
        INNER JOIN configuracion_notificacion cn 
        ON cn.ruc_empresa_numero = c.ruc
        LEFT JOIN pdt_renta pr 
        ON pr.ruc_empresa = c.ruc 
        AND pr.anio = $anio
        AND pr.estado = 1

        WHERE 
        (cn.id_tributo = 1 or cn.id_tributo = 3 or cn.id_tributo = 4)
        AND c.estado = $estado 
        AND (c.razon_social LIKE '%$search%' OR c.ruc like '%$search%')

        GROUP BY 
        c.ruc, c.razon_social
        $order;")->getResultArray();

        return $this->response->setJSON($data);
    }
}
