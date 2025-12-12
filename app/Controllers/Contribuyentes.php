<?php

namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

use App\Controllers\BaseController;

use App\Models\UbigeoModel;
use App\Models\ContribuyenteModel;
use App\Models\SistemaContribuyenteModel;
use App\Models\SistemaModel;
use App\Models\HistorialTarifaModel;
use App\Models\CertificadoDigitalModel;
use App\Models\PagosModel;
use App\Models\CodificacionModel;
use App\Models\ConfiguracionNotificacionModel;
use App\Models\DeclaracionSunatModel;
use App\Models\DeclaracionModel;
use App\Models\PdtModel;
use App\Models\TributoModel;
use App\Models\UitModel;
use App\Models\PrefijosModel;
use App\Models\ContactosContribuyenteModel;
use App\Models\MigracionModel;
use App\Models\MigrarModel;
use App\Models\ComprobanteModel;
use App\Models\AyudaBoletaModel;
use App\Models\NumeroWhatsappModel;
use App\Models\ContratosModel;
use App\Models\ServidorModel;
use App\Models\RucModel;

//use App\Models\RucEmpresaModel;

use DateTime;

class Contribuyentes extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $sistema = new SistemaModel();
        $sistemas = $sistema->where('status', 1)->findAll();

        $consulta_certificado_por_vencer = $this->certificados_por_vencer();

        $numeros = new NumeroWhatsappModel();
        $numeros_whatsapp = $numeros->where('estado', 1)->findAll();

        $menu = $this->permisos_menu();

        $crear = $this->getPermisosAcciones(27, session()->perfil_id, 'crear');

        return view('contribuyente/lista', compact('sistemas', 'consulta_certificado_por_vencer', 'menu', 'numeros_whatsapp', 'crear'));
    }

    public function getIdContribuyente($id)
    {
        $cont = new ContribuyenteModel();

        $data = $cont->select('id, ruc, razon_social')->find($id);

        return $this->response->setJSON($data);
    }

    public function getContribuyenteActivos($type)
    {
        $cont = new ContribuyenteModel();

        if ($type == 'TODOS') {
            $contribuyentes = $cont->where('estado', 1)->orderBy('RIGHT(ruc, 1) ASC')->findAll();
            return $this->response->setJSON($contribuyentes);
        }

        $contribuyentes = $cont->where('estado', 1)->where('tipoServicio', $type)->orderBy('RIGHT(ruc, 1) ASC')->findAll();

        return $this->response->setJSON($contribuyentes);
    }

    public function allCobros()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('contribuyente/cobros', compact('menu'));
    }

    public function listaUbigeo()
    {
        $model = new UbigeoModel();
        $data = $model->allUbigeo();
        return $this->response->setJSON($data);
    }

    public function renderContribuyentes()
    {
        $cont = new ContribuyenteModel();

        $contribuyentes = $cont->where('estado >', 0)->findAll();

        return $this->response->setJSON($contribuyentes);
    }

    public function renderContribuyentesContables()
    {
        $cont = new ContribuyenteModel();

        $contribuyentes = $cont->where("tipoServicio", "CONTABLE")->where('estado >', 0)->findAll();

        return $this->response->setJSON($contribuyentes);
    }

    public function contribuyentesContables($estado)
    {
        $cont = new ContribuyenteModel();

        $contribuyentes = $cont->where("tipoServicio", "CONTABLE")->where('estado', $estado)->findAll();

        return $this->response->setJSON($contribuyentes);
    }

    public function listaContribuyentes($filtro, $estado)
    {
        $model = new ContribuyenteModel();
        $confiNoti = new ConfiguracionNotificacionModel();
        $declaracionSunat = new DeclaracionSunatModel();
        $tributo = new TributoModel();
        $uit_ = new UitModel();

        $eliminar = $this->getPermisosAcciones(27, session()->perfil_id, 'eliminar');
        $editar = $this->getPermisosAcciones(27, session()->perfil_id, 'editar');

        $isDeleted = false;

        if ($eliminar) {
            $isDeleted = true;
        }

        $isEdit = false;

        if ($editar) {
            $isEdit = true;
        }

        $sql = "";
        $asig = "";
        $status = "";

        if ($filtro !== 'TODOS') {
            $sql = "AND c.tipoServicio = '$filtro'";
        }

        /*if (session()->perfil_id > 2) {
            $asig = " AND cu.usuario_id = " . session()->id;
        }*/

        if ($estado == 0) {
            $status = "c.estado > 0";
        } else {
            $status = "c.estado = $estado";
        }

        $data = $model->query("SELECT 
            c.*, 
            -- Verificar si tiene sistema
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM sistemas_contribuyente sc 
                    WHERE sc.contribuyente_id = c.id
                ) THEN 'SI'
                ELSE 'NO'
            END AS tiene_sistema,
            -- Verificar si tiene certificado digital
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM certificado_digital cd 
                    WHERE cd.contribuyente_id = c.id and cd.estado = 1
                ) THEN 'SI'
                ELSE 'NO'
            END AS tiene_certificado,
            -- Verificar si el certificado está vencido
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM certificado_digital cd 
                    WHERE cd.contribuyente_id = c.id and cd.estado = 1
                    AND cd.fecha_vencimiento >= CURDATE()
                ) THEN 'NO' -- Tiene un certificado válido
                ELSE 'SI' -- No tiene certificado válido o está vencido
            END AS certificado_vencido
        FROM contribuyentes c left join contribuyentes_usuario cu ON cu.contribuyente_id = c.id WHERE $status $sql $asig order by c.id desc")->getResult();

        foreach ($data as $key => $value) {
            $confNot = $confiNoti->where('ruc_empresa_numero', $value->ruc)->orderBy('id_tributo', 'asc')->findAll();

            $mes = date('m');
            $dia = date('d');
            $anio = date('Y');

            if ($mes < 2) {
                $anio = $anio - 1;
            } else {

                $anio = $anio;
            }

            if ($confNot) {
                $id = $confNot[0]["id_tributo"];

                $dataDeclaracion = $declaracionSunat->query("SELECT SUM(decl_sunat_importe_venta) as venta,SUM(decl_sunat_importe_compra) as compra,SUM(monto) as monto FROM declaracion_sunat INNER JOIN fecha_declaracion ON declaracion_sunat.id_fecha_declaracion = fecha_declaracion.id_fecha_declaracion INNER JOIN anio ON fecha_declaracion.id_anio = anio.id_anio WHERE fecha_declaracion.id_tributo= $id and declaracion_sunat.ruc_empresa_numero= $value->id and anio_descripcion = $anio")->getResult();

                $venta = $dataDeclaracion[0]->venta;
                $compra = $dataDeclaracion[0]->compra;
                $monto = $dataDeclaracion[0]->monto;
                $utilidad = (float) $venta - (float) $compra;
                $cantidad = 0;

                switch ($id) {
                    case 1:
                        $codigo = $tributo->find(12);
                        $primero = $codigo["porcentaje_renta"];
                        $cantidad = ($utilidad * $primero / 100) - $monto;
                        $value->tipo = "general";

                        break;
                    case 2:
                        $cantidad = $utilidad;
                        $value->tipo = "especial";
                        break;
                    case 3:
                        $codigo = $tributo->find(11);
                        $primero = $codigo["porcentaje_renta"];
                        $cantidad = ($utilidad * $primero / 100) - $monto;
                        $value->tipo = "amazonia";
                        break;
                    case 4:
                        $sum = $uit_->findAll();
                        $uit = $sum[0]["uit_monto"];
                        $uitotal = (float) $uit * 15;
                        $codigo = $tributo->find(13);
                        $primero = $codigo["porcentaje_renta"];
                        $segundo = $codigo["porcentaje_renta_segunda"];
                        $value->tipo = "mype";
                        $sub = 0;
                        if ($utilidad <= $uitotal) {
                            $cantidad = ($utilidad * $primero / 100) - $monto;
                        } else {
                            $sub = 0;
                            $sub = $utilidad - $uitotal;
                            $cantidad = (($uitotal * $primero / 100) + ($sub * $segundo / 100)) - $monto;
                        }
                        break;
                    case 5:
                        $codigo = $tributo->find(14);
                        $primero = $codigo["porcentaje_renta"];
                        $cantidad = ($utilidad * $primero / 100) - $monto;
                        $value->tipo = "agrario";
                        break;
                    default:
                        $cantidad = 0;
                        $value->tipo = "falta configuracion";
                        break;
                }

                $normal = $value->ruc_empresa_normal . " ";
                $baja = $value->ruc_empresa_baja . " ";
                $media = $value->ruc_empresa_medio . " ";

                $cantidad . " ";

                if ($cantidad != 0) {
                    if ((strlen($normal) != "1" && strlen($media) != "1" && strlen($baja) != "1")) {
                        if ($cantidad < $normal) {
                            $value->respuesta = 1;
                        } else {
                            if ($cantidad < $media) {
                                $value->respuesta = 2;
                            } else {

                                $value->respuesta = 3;
                            }
                        }
                    } else {
                        $value->respuesta = 0;
                    }
                } else {

                    $value->respuesta = 0;
                }
            } else {
                $value->respuesta = 3;
                $value->tipo = "Falta configuración";
            }
        }

        $array = [
            "eliminar" => $isDeleted,
            "editar" => $isEdit,
            "data" => $data
        ];

        return $this->response->setJSON($array);
    }

    public function guardar()
    {
        $sistema = new SistemaContribuyenteModel();
        $model = new ContribuyenteModel();
        $codificacion = new CodificacionModel();
        $contrato = new ContratosModel();
        $contacto = new ContactosContribuyenteModel();

        $model->db->transStart();

        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            /*$verificar_api_cabecera_contrato = $this->apiLoadContrato($ruta_contrato);

            if ($data['tipoServicio'] === 'CONTABLE') {
                if ($verificar_api_cabecera_contrato['first_line'] != "CONTRATO DE SERVICIOS CONTABLES") {
                    unlink($ruta_contrato);
                    return $this->response->setJSON(['status' => 'error', 'message' => "El contrato no es válido"]);
                }
            } else {
                if ($verificar_api_cabecera_contrato['first_line'] != "CONTRATO DE SERVICIOS DE SOFTWARE INFORMATICA") {
                    unlink($ruta_contrato);
                    return $this->response->setJSON(['status' => 'error', 'message' => "El contrato no es válido"]);
                }
            }*/

            $sistemas = "";

            if (isset($data['nameSystem'])) {
                $sistemas = $data['nameSystem'];
            }

            $idTabla = $data['idTable'];

            $verificar = $model->where('ruc', $data['numeroDocumento'])->first();

            if ($data['tipoSuscripcion'] === 'GRATUITO') {
                $diacobro = 0;
            } else {
                $diacobro = $data['diaCobro'];
            }

            $datos = [
                'ruc' => $data['numeroDocumento'],
                'razon_social' => $data['razonSocial'],
                'nombre_comercial' => $data['nombreComercial'],
                'direccion_fiscal' => $data['direccionFiscal'],
                'ubigeo_id' => $data['ubigeo'],
                'urbanizacion' => $data['urbanizacion'],
                'tipoSuscripcion' => $data['tipoSuscripcion'],
                'tipoServicio' => $data['tipoServicio'],
                'tipoPago' => $data['tipoPago'],
                'costoMensual' => $data['costoMensual'],
                'costoAnual' => $data['costoAnual'],
                'diaCobro' => $diacobro,
                'diaSuscripcion' => $data['diaSuscripcion'],
                'fechaContrato' => $data['fechaContrato'],
                'telefono' => "",
                'correo' => "",
                'usuario_secundario' => "",
                'clave_usuario_secundario' => "",
                'acceso' => $data['numeroDocumento'],
                'estado' => 1,
                'numeroWhatsappId' => $data['numeroNotificacion'],
                'monto_servidor' => isset($data['monto_servidor']) ? $data['monto_servidor'] : 0,
            ];

            $clientesVarios = $data['clientesVarios'];
            $boletaAnulado = $data['boletaAnulado'];
            $facturaAnulado = $data['facturaAnulado'];

            $tarifa = new HistorialTarifaModel();

            if ($idTabla === "0") {

                $file_contrato = $this->request->getFile('contrato');

                if (!$file_contrato) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'No se recibió ningún archivo de contrato']);
                }

                if (!$file_contrato->isValid()) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'No es un archivo válido']);
                }

                if ($file_contrato->getClientMimeType() !== 'application/pdf') {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permite archivo PDF']);
                }

                $ext_contrato = $file_contrato->getExtension();
                $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

                $archivo_contrato = "CONTRATO_" . $data['numeroDocumento'] . "_" . $codigo . "." . $ext_contrato;

                $file_contrato->move(FCPATH . 'contratos', $archivo_contrato);

                $ruta_contrato = FCPATH . 'contratos/' . $archivo_contrato;

                if ($verificar) {
                    return $this->response->setJSON(['status' => 'error', 'message' => "El RUC ya se encuentra registrado."]);
                }

                $model->insert($datos);

                $contribuyente_id = $model->insertID();

                $model->update($contribuyente_id, ['user_add' => session()->id]);

                $data_contacto = array(
                    'nombre_contacto' => $data['nameContact'],
                    'telefono' => $data['numero_what'],
                    'prefijo' => "51",
                    'numero_whatsapp' => "51" . $data['numero_what'],
                    'correo' => "",
                    'estado' => 1,
                    'contribuyente_id' => $contribuyente_id
                );

                $contacto->insert($data_contacto);

                if (isset($data['nameSystem'])) {
                    for ($i = 0; $i < count($sistemas); $i++) {
                        $sistema->insert([
                            'contribuyente_id' => $contribuyente_id,
                            'system_id' => $sistemas[$i]
                        ]);
                    }
                }

                $fechaInit = new DateTime($data['fechaContrato']);
                $fechaInicio = $fechaInit->format('Y-m') . "-" . $data['diaCobro'];

                $dataContrato = [
                    'contribuyenteId' => $contribuyente_id,
                    'fechaInicio' => $data['fechaContrato'],
                    'fechaFin' => "0000-00-00",
                    'diaCobro' => $data['diaCobro'],
                    'file' => $archivo_contrato,
                    'estado' => 1,
                ];

                $contrato->insert($dataContrato);

                $idContrato = $contrato->insertID();

                $tarifa->insert([
                    'contratoId' => $idContrato,
                    'fecha_inicio' => $fechaInicio,
                    'monto_mensual' => $data['costoMensual'],
                    'monto_anual' => $data['costoAnual'],
                    'estado' => 1
                ]);

                //agregar servidor monto
                if (isset($data['monto_servidor'])) {
                    $data_servidor = [
                        'contribuyente_id' => $contribuyente_id,
                        'fecha_inicio' => $data['fechaContrato'],
                        'fecha_fin' => '',
                        'monto' => $data['monto_servidor'],
                        'estado' => 1
                    ];

                    $servidor = new ServidorModel();
                    $servidor->insert($data_servidor);
                }

                $codificacion->insert([
                    'contribuyente_id' => $contribuyente_id,
                    'id_tipo_comprobante' => 1,
                    'id_codigo_tipo' => 2,
                    'codificacion_numero' => $clientesVarios
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $contribuyente_id,
                    'id_tipo_comprobante' => 1,
                    'id_codigo_tipo' => 1,
                    'codificacion_numero' => $boletaAnulado
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $contribuyente_id,
                    'id_tipo_comprobante' => 5,
                    'id_codigo_tipo' => 2,
                    'codificacion_numero' => $clientesVarios
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $contribuyente_id,
                    'id_tipo_comprobante' => 5,
                    'id_codigo_tipo' => 1,
                    'codificacion_numero' => $boletaAnulado
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $contribuyente_id,
                    'id_tipo_comprobante' => 2,
                    'id_codigo_tipo' => 1,
                    'codificacion_numero' => $facturaAnulado
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $contribuyente_id,
                    'id_tipo_comprobante' => 3,
                    'id_codigo_tipo' => 2,
                    'codificacion_numero' => $clientesVarios
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $contribuyente_id,
                    'id_tipo_comprobante' => 3,
                    'id_codigo_tipo' => 1,
                    'codificacion_numero' => $boletaAnulado
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $contribuyente_id,
                    'id_tipo_comprobante' => 4,
                    'id_codigo_tipo' => 1,
                    'codificacion_numero' => $facturaAnulado
                ]);

                $model->db->transComplete();

                if ($model->db->transStatus() === false) {
                    throw new \Exception("Error al realizar la operación.");
                }

                return $this->response->setJSON(['status' => 'success', 'message' => "Contribuyente registrado correctamente."]);
            } else {
                $model->update($idTabla, $datos);

                $model->update($idTabla, ['user_edit' => session()->id]);

                $sistema->where('contribuyente_id', $idTabla)->delete();

                if (isset($data['nameSystem'])) {
                    for ($i = 0; $i < count($sistemas); $i++) {
                        $sistema->insert([
                            'contribuyente_id' => $idTabla,
                            'system_id' => $sistemas[$i]
                        ]);
                    }
                }

                $dataContrato = $contrato->where('contribuyenteId', $idTabla)->where('estado', 1)->first();

                $idContrato = $dataContrato['id'];

                $dataContratoUpdate = [
                    'fechaInicio' => $data['fechaContrato'],
                    'diaCobro' => $data['diaCobro']
                ];

                $contrato->update($idContrato, $dataContratoUpdate);

                $tarifaData = $tarifa->where('contratoId', $idContrato)->orderBy('id', 'asc')->first();

                $idTarifa = $tarifaData['id'];

                $dataTarifa = array(
                    "monto_mensual" => $data['costoMensual'],
                    "monto_anual" => $data['costoAnual']
                );

                $tarifa->update($idTarifa, $dataTarifa);

                $codificacion->where('contribuyente_id', $idTabla)->delete();

                $codificacion->insert([
                    'contribuyente_id' => $idTabla,
                    'id_tipo_comprobante' => 1,
                    'id_codigo_tipo' => 2,
                    'codificacion_numero' => $clientesVarios
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $idTabla,
                    'id_tipo_comprobante' => 1,
                    'id_codigo_tipo' => 1,
                    'codificacion_numero' => $boletaAnulado
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $idTabla,
                    'id_tipo_comprobante' => 5,
                    'id_codigo_tipo' => 2,
                    'codificacion_numero' => $clientesVarios
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $idTabla,
                    'id_tipo_comprobante' => 5,
                    'id_codigo_tipo' => 1,
                    'codificacion_numero' => $boletaAnulado
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $idTabla,
                    'id_tipo_comprobante' => 2,
                    'id_codigo_tipo' => 1,
                    'codificacion_numero' => $facturaAnulado
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $idTabla,
                    'id_tipo_comprobante' => 3,
                    'id_codigo_tipo' => 2,
                    'codificacion_numero' => $clientesVarios
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $idTabla,
                    'id_tipo_comprobante' => 3,
                    'id_codigo_tipo' => 1,
                    'codificacion_numero' => $boletaAnulado
                ]);

                $codificacion->insert([
                    'contribuyente_id' => $idTabla,
                    'id_tipo_comprobante' => 4,
                    'id_codigo_tipo' => 1,
                    'codificacion_numero' => $facturaAnulado
                ]);

                $model->db->transComplete();

                if ($model->db->transStatus() === false) {
                    throw new \Exception("Error al realizar la operación.");
                }

                return $this->response->setJSON(['status' => 'success', 'message' => "Contribuyente editado correctamente."]);
            }
        } catch (\Exception $e) {
            $model->db->transRollback();

            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getContribuyente($id)
    {
        $model = new ContribuyenteModel();
        $data = $model->find($id);

        $sistema = new SistemaContribuyenteModel();
        $sistemas = $sistema->where('contribuyente_id', $id)->findAll();

        return $this->response->setJSON(['status' => 'success', 'data' => $data, 'sistemas' => $sistemas]);
    }

    public function getTarifaContribuyente($id)
    {
        $tarifa = new HistorialTarifaModel();
        $contri = new ContribuyenteModel();
        $contrato = new ContratosModel();

        $dataContrato = $contrato->where('contribuyenteId', $id)->where('estado', 1)->orderBy('id', 'desc')->first();

        $idContrato = $dataContrato['id'];

        $data_tarifa = $tarifa->where('contratoId', $idContrato)->where('estado', 1)->orderBy('id', 'desc')->findAll();
        $data_contribuyente = $contri->find($id);

        return $this->response->setJSON(['status' => 'success', 'data_tarifa' => $data_tarifa, 'data_contribuyente' => $data_contribuyente]);
    }

    public function getCertificadoDigital($id)
    {
        $certificado = new CertificadoDigitalModel();
        $contri = new ContribuyenteModel();

        $data_certificado = $certificado->where('contribuyente_id', $id)->where('estado !=', 0)->orderBy('id', 'desc')->findAll();
        $data_contribuyente = $contri->find($id);

        return $this->response->setJSON(['status' => 'success', 'data_certificado' => $data_certificado, 'data_contribuyente' => $data_contribuyente]);
    }

    public function guardarTarifa()
    {
        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $tarifa = new HistorialTarifaModel();
            $contri = new ContribuyenteModel();
            $contrato = new ContratosModel();

            $idContribuyente = $data['idTableTarifa'];
            $fechaInicio = $data['fechaInicioTarifa'];

            $dataContrato = $contrato->where('contribuyenteId', $idContribuyente)->where('estado', 1)->first();

            $idContrato = $dataContrato['id'];

            $dataContri = $contri->select('diaCobro')->find($idContribuyente);

            $fechaInicio = $fechaInicio . "-" . $dataContri['diaCobro'];

            $last_tarifa = $tarifa->where('contratoId', $idContrato)->where('estado', 1)->orderBy('fecha_inicio', 'DESC')->first();

            if ($fechaInicio <= $last_tarifa['fecha_inicio']) {
                return $this->response->setJSON(['status' => 'error', 'message' => "No puedes colocar una fecha menor o igual a la ultima fecha de la tarifa"]);
            }

            if ($last_tarifa) {
                $tarifa->update($last_tarifa['id'], ['fecha_fin' => $fechaInicio]);
            }

            $tarifa->insert([
                'contratoId' => $idContrato,
                'fecha_inicio' => $fechaInicio,
                'monto_mensual' => $data['montoMensualTarifa'],
                'monto_anual' => $data['montoAnualTarifa'],
                'estado' => 1
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => "Tarifa registrada correctamente."]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteTarifa($id)
    {
        $model = new HistorialTarifaModel();

        $data = array('estado' => 0);

        $model->update($id, $data);

        return $this->response->setJSON(['status' => 'success', 'message' => "Tarifa eliminada correctamente."]);
    }

    public function listaHonorariosCobros($select, $estado)
    {
        $model = new ContribuyenteModel();
        $pago = new PagosModel();

        $cobrar = $this->getPermisosAcciones(13, session()->perfil_id, 'cobrar honorario');

        $sql = "";

        if ($select !== 'TODOS') {
            $sql = "AND c.tipoServicio = '$select'";
        }

        $datos = $model->query("SELECT c.id, c.razon_social, c.ruc, c.tipoPago, c.diaCobro, c.tipoServicio, c.tipoSuscripcion FROM contribuyentes as c WHERE tipoSuscripcion = 'NO GRATUITO' AND c.estado = $estado $sql ORDER BY c.id desc")->getResult();

        foreach ($datos as $key => $value) {
            $id = $value->id;

            //si tiene pagos
            $pagos = $pago->where('contribuyente_id', $id)->findAll();
            $amortizo = 0;

            if (!$pagos) {
                $debe = "No tiene pagos";
            } else {
                if ($value->tipoPago == 'ATRASADO') {
                    $maxPago = $pago->query("SELECT MAX(mesCorrespondiente) as ultimoMes FROM pagos WHERE contribuyente_id = $id AND estado = 'pagado' ")->getRow();

                    $max = $pago->query("SELECT MAX(estado) as estado FROM pagos WHERE contribuyente_id = $id ")->getRow();

                    if ($max->estado === 'pendiente') {
                        $amortizo = 1;
                    }

                    $ultimo = $maxPago->ultimoMes;
                    //$ultimo = "2025-04-28";

                    $ultimoPagoFecha = date('Y-m', strtotime($ultimo));
                    $fechaActual = date('Y-m');

                    list($ultimoAnio, $ultimoMes) = explode('-', $ultimoPagoFecha);
                    list($actualAnio, $actualMes) = explode('-', $fechaActual);

                    $ultimoAnio = (int) $ultimoAnio;
                    $ultimoMes = (int) $ultimoMes;
                    $actualAnio = (int) $actualAnio;
                    $actualMes = (int) $actualMes;

                    // Calcular la diferencia en meses
                    $mesesTranscurridos = ($actualAnio - $ultimoAnio) * 12 + ($actualMes - $ultimoMes);

                    // Restar 1 porque el mes actual no cuenta (aún no ha terminado)
                    $meses = max(0, $mesesTranscurridos - 1);

                    if ($meses > 1) {
                        $debe = $meses . " meses";
                    } elseif ($meses == 1) {
                        $debe = $meses . " mes";
                    } else {
                        $debe = "No debe";
                    }
                } else {
                    $ultimoPago = $pago->query("SELECT MAX(mesCorrespondiente) as ultimoMes FROM pagos WHERE contribuyente_id = $id AND estado = 'pagado' ")->getRow();

                    $max = $pago->query("SELECT MAX(estado) as estado FROM pagos WHERE contribuyente_id = $id ")->getRow();

                    if ($max->estado === 'pendiente') {
                        $amortizo = 1;
                    }

                    if ($ultimoPago && !empty($ultimoPago->ultimoMes)) {
                        // Convertimos a objeto DateTime
                        $fechaUltimoPago = DateTime::createFromFormat('Y-m-d', $ultimoPago->ultimoMes);
                        $fechaActual = new DateTime();

                        if ($fechaUltimoPago) {
                            // Normaliza ambos al primer día del mes
                            $inicioUltimoMes = DateTime::createFromFormat('Y-m-d', $fechaUltimoPago->format('Y-m-01'));
                            $inicioMesActual = DateTime::createFromFormat('Y-m-d', $fechaActual->format('Y-m-01'));

                            if ($inicioUltimoMes && $inicioMesActual) {
                                $diferencia = (($inicioMesActual->format('Y') - $inicioUltimoMes->format('Y')) * 12) +
                                    ($inicioMesActual->format('m') - $inicioUltimoMes->format('m'));

                                if ($diferencia > 1) {
                                    $debe = $diferencia . " meses";
                                } elseif ($diferencia == 1) {
                                    $debe = $diferencia . " mes";
                                } else {
                                    $debe = "No debe";
                                }
                            } else {
                                $debe = "Error al normalizar las fechas.";
                            }
                        } else {
                            $debe = "Fecha inválida en último pago.";
                        }

                        //echo "Meses de deuda: " . $diferencia;
                    } else {
                        // No tiene pagos registrados, asumes que debe desde el inicio o todos
                        //echo "No tiene pagos registrados. Se asume que debe todos los meses.";
                        $debe = "No debe";
                    }
                }
            }

            $cobrarHono = "";

            if ($cobrar) {
                $cobrarHono = '<a href="' . base_url() . 'pago-honorario/' . $value->id . '" class="btn btn-success">COBRAR</a>';
            }

            $value->debe = $debe;
            $value->amortizo = $amortizo;
            $value->cobrar = $cobrarHono;
        }

        return $this->response->setJSON($datos);
    }

    public function guardarCertificadoDigital()
    {
        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $certificado = new CertificadoDigitalModel();

            $clave = "";
            $ruta = "";
            $nameFile = "";

            if ($data['tipo_certificado'] === 'PROPIO') {
                if ($this->request->getFile('file_certificado')->isValid() && !$this->request->getFile('file_certificado')->hasMoved()) {

                    $archivo = $this->request->getFile('file_certificado');

                    $nombreOriginal = $archivo->getClientName();
                    $extension = $archivo->getClientExtension();

                    if (!in_array($extension, ['pfx', 'cer', 'p12'])) {
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Solo se permiten archivos con extensión .pfx o .cer.']);
                    }

                    $archivo->move(WRITEPATH . 'uploads/certificadoDigital/', $nombreOriginal);

                    // Ruta donde se guardó el archivo
                    $rutaArchivo = WRITEPATH . 'uploads/certificadoDigital/' . $nombreOriginal;

                    $traer_ultimo = $certificado->where('contribuyente_id', $data['idTableCertificado'])->orderBy('id', 'DESC')->first();

                    if ($traer_ultimo) {
                        $actualizar = array("estado" => 2);

                        $certificado->update($traer_ultimo['id'], $actualizar);
                    }

                    $codigoAleatorio = bin2hex(random_bytes(4));

                    $clave = $data['claveCertificado'];
                    $ruta = $rutaArchivo;
                    $nameFile = $codigoAleatorio . "_" . $nombreOriginal;
                } else {
                    // Manejar el caso donde no se envió un archivo válido
                    $archivo = null;
                    // Opcional: Mensaje de error
                    $error = $this->request->getFile('archivo')->getErrorString();

                    return $this->response->setJSON(['status' => 'error', 'message' => $error]);
                }
            } else {
                $traer_ultimo = $certificado->where('contribuyente_id', $data['idTableCertificado'])->where('estado !=', 0)->orderBy('id', 'DESC')->first();

                if ($traer_ultimo) {
                    $actualizar = array("estado" => 2);

                    $certificado->update($traer_ultimo['id'], $actualizar);
                }
            }

            $certificado->insert([
                'contribuyente_id' => $data['idTableCertificado'],
                'tipo_certificado' => $data['tipo_certificado'],
                'fecha_inicio' => $data['fechaInicioCertificado'],
                'fecha_vencimiento' => $data['fechaVencimientoCertificado'],
                'clave' => $clave,
                'ruta' => $ruta,
                'nameFile' => $nameFile,
                'estado' => 1
            ]);

            return $this->response->setJSON(['status' => 'success', 'message' => "Certificado registrado correctamente."]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function descargarCertificado($nameFile)
    {
        // Ruta completa del archivo
        $rutaArchivo = WRITEPATH . 'uploads/certificadoDigital/' . $nameFile;

        // Verificar si el archivo existe
        if (file_exists($rutaArchivo)) {
            // Descargar el archivo
            return $this->response->download($rutaArchivo, null);
        } else {
            // Retornar error si no se encuentra el archivo
            return $this->response->setJSON(['status' => 'error', 'message' => 'El archivo no existe.']);
        }
    }

    public function deleteCertificadoDigital($id)
    {
        $model = new CertificadoDigitalModel();

        $data = array("estado" => 0);

        $model->update($id, $data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'El elimino correctamente el certificado']);
    }

    public function changeStatus($id, $status)
    {
        $model = new ContribuyenteModel();

        if ($status !== 1) {
            $data = array('estado' => $status, 'deleted_at' => date('Y-m-d H:i:s:s'));
            $model->update($id, $data);
        } else {
            $data = array('estado' => $status);
            $model->update($id, $data);
        }

        $message = "SE DESACTIVO EL CONTRIBUYENTE CORRECTAMENTE";

        if ($status == 1) {
            $message  = "SE ACTIVO EL CONTRIBUYENTE CORRECTAMENTE";
        }

        return $this->response->setJSON(['status' => 'success', 'message' => $message]);
    }

    public function prefijosPaises()
    {
        $prefijo = new PrefijosModel();

        $prefijos = $prefijo->findAll();

        return $this->response->setJSON($prefijos);
    }

    public function addContacto()
    {
        $contacto = new ContactosContribuyenteModel();

        try {
            $codigo = $this->request->getPost('selectPais');
            $numeroWhatsapp = $this->request->getPost('numero_whatsapp');
            $numero_llamadas = $this->request->getPost('numero_llamadas');
            $nombre_contacto = $this->request->getPost('nombre_contacto');
            $correo = $this->request->getPost('correo');
            $contribuyente_id = $this->request->getPost('contribuyente_id');
            $contacto_id = $this->request->getPost('contacto_id');

            $data = array(
                'nombre_contacto' => $nombre_contacto,
                'telefono' => $numero_llamadas,
                'prefijo' => $codigo,
                'numero_whatsapp' => $codigo . $numeroWhatsapp,
                'correo' => $correo,
                'estado' => 1,
                'contribuyente_id' => $contribuyente_id
            );

            if ($contacto_id == 0) {
                $contacto->insert($data);

                return $this->response->setJSON(['status' => 'success', 'message' => 'Contacto registrado correctamente.']);
            } else {
                $contacto->update($contacto_id, $data);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Contacto actualizado correctamente.']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function renderContactos($id)
    {
        $contacto = new ContactosContribuyenteModel();
        $contri = new ContribuyenteModel();

        $contactos = $contacto->where('contribuyente_id', $id)->where('estado', 1)->findAll();

        $data_contribuyente = $contri->find($id);

        return $this->response->setJSON([
            "contactos" => $contactos,
            "data_contribuyente" => $data_contribuyente
        ]);
    }

    public function getContacto($id)
    {
        $contacto = new ContactosContribuyenteModel();

        $data = $contacto->find($id);

        return $this->response->setJSON($data);
    }

    public function deleteContacto($id)
    {
        $contacto = new ContactosContribuyenteModel();

        $contacto->update($id, ['estado' => 0]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Contacto eliminado correctamente.']);
    }

    public function deleteContribuyente($id)
    {
        $model = new ContribuyenteModel();

        $data = array('estado' => 0);

        $model->update($id, $data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Contribuyente eliminado correctamente.']);
    }

    public function declaracion($id)
    {
        $contrib = new ContribuyenteModel();
        $tributo = new TributoModel();
        $declaracion = new DeclaracionModel();
        $pdt = new PdtModel();
        $conf = new ConfiguracionNotificacionModel();

        $data_contrib = $contrib->select('ruc, razon_social')->find($id);

        $ruc = $data_contrib['ruc'];

        $declaraciones = $declaracion->where('decl_estado', 1)->findAll();

        foreach ($declaraciones as $key => $value) {

            $pdts = $pdt->where('pdt_estado', 1)->where('id_declaracion', $value['id_declaracion'])->findAll();

            foreach ($pdts as $key1 => $value1) {

                $tributos = $tributo->where('id_pdt', $value1['id_pdt'])->findAll();

                foreach ($tributos as $key2 => $value2) {
                    $configuracion = $conf->where('id_tributo', $value2['id_tributo'])->where('ruc_empresa_numero', $ruc)->first();

                    if ($configuracion) {
                        $tributos[$key2]['configuracion'] = 1;
                    } else {
                        $tributos[$key2]['configuracion'] = 0;
                    }
                }

                $pdts[$key1]['tributos'] = $tributos;
            }

            $declaraciones[$key]['pdts'] = $pdts;
        }

        return $this->response->setJSON([
            "configuraciones" => $declaraciones,
            "contribuyente" => $data_contrib
        ]);
    }

    public function configurarDeclaracion()
    {
        $configuracion = new ConfiguracionNotificacionModel();

        try {
            $declaracion = $this->request->getPost('declaracion');
            $ruc = $this->request->getPost('ruc_empresa');

            $configuracion->where('ruc_empresa_numero', $ruc)->delete();

            for ($i = 0; $i < count($declaracion); $i++) {
                $data = array(
                    "id_tributo" => $declaracion[$i],
                    "ruc_empresa_numero" => $ruc
                );

                $configuracion->insert($data);
            }

            return $this->response->setJSON(['status' => 'success', 'message' => 'Configuración guardada correctamente.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function download($filename)
    {
        $filePath = WRITEPATH . 'temp/' . $filename;

        if (!file_exists($filePath)) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'Archivo no encontrado'
            ]);
        }

        // Descargar y eliminar archivo temporal
        $response = $this->response->download($filePath, null);
        register_shutdown_function(function () use ($filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        });

        return $response;
    }

    private function generateExcel($idMigracion)
    {
        $migrar = new MigrarModel();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar contenido
        $sheet->setCellValue('A1', 'FECHA');
        $sheet->setCellValue('B1', 'TIPO_MONEDA');
        $sheet->setCellValue('C1', 'DOCUMENTO');
        $sheet->setCellValue('D1', '#_DOCUMENTO');
        $sheet->setCellValue('E1', 'CONDICION');
        $sheet->setCellValue('F1', 'RUC');
        $sheet->setCellValue('G1', 'RAZON_SOCIAL');
        $sheet->setCellValue('H1', 'VVENTA');
        $sheet->setCellValue('I1', 'VALOR_DE_VENTA');
        $sheet->setCellValue('J1', 'IGV');
        $sheet->setCellValue('K1', 'ICBPER');
        $sheet->setCellValue('L1', 'TOTAL');
        $sheet->setCellValue('M1', 'TIPO_CAMBIO');

        $series_factura = $migrar->select('serie')->where('id_migracion', $idMigracion)->where('tipo', '01')->groupBy('serie')->findAll();

        $series_boleta = $migrar->select('serie')->where('id_migracion', $idMigracion)->where('tipo', '03')->groupBy('serie')->findAll();

        $maqueta = [];

        $tipo_moneda = 'S';
        $tipo_cambio = 1;

        foreach ($series_factura as $serie_factura) {
            $facturas = $migrar->where('id_migracion', $idMigracion)->where('tipo', '01')->where('serie', $serie_factura['serie'])->orderBy('fecha', 'asc')->orderBy('numero', 'asc')->findAll();

            foreach ($facturas as $factura) {
                $subtotal = $factura['monto'] - $factura['icbper'];

                $add = array(
                    "fecha" => $factura['fecha'],
                    "tipo_moneda" => $tipo_moneda,
                    "documento" => $factura['comprobante_tipo'],
                    "numero_documento" => $factura['serie'] . "-" . $factura['numero'],
                    "condicion" => 'A',
                    "ruc" => $factura['ruc'],
                    "razon_social" => $factura['razon_social'],
                    "vvventa" => $subtotal,
                    "valor_venta" => $subtotal,
                    "igv" => $factura['igv'],
                    "icbper" => $factura['icbper'],
                    "total" => $factura['monto'],
                    "tipo_cambio" => $tipo_cambio
                );

                array_push($maqueta, $add);
            }
        }

        foreach ($series_boleta as $serie_boleta) {

            $boletas = $migrar->where('id_migracion', $idMigracion)->where('tipo', '03')->where('serie', $serie_boleta['serie'])->orderBy('fecha', 'asc')->orderBy('numero', 'asc')->findAll();

            $grupoActual = null;

            foreach ($boletas as $fila) {
                if ($fila['monto'] >= 700) {
                    // Si hay un grupo pendiente, agregarlo primero
                    if ($grupoActual !== null) {
                        $maqueta[] =  $this->finalizarGrupo($grupoActual);
                        $grupoActual = null;
                    }

                    $add = $this->agregarFila($fila);
                    array_push($maqueta, $add);

                    continue;
                }

                // Si no hay grupo actual, iniciar uno nuevo
                if ($grupoActual === null) {
                    $grupoActual = [
                        'fecha' => $fila['fecha'],
                        'serie' => $fila['serie'],
                        'nums' => [$fila['numero']],
                        'cond' => 'A',
                        'monto' => $fila['monto'],
                        'num_clie' => $fila['ruc'],
                        'nombre_clien' => $fila['razon_social'],
                        'subtotal' => $fila['valor_venta'],
                        'total_igv' => $fila['igv'],
                        'total_icbper' => $fila['icbper']
                    ];

                    continue;
                }

                // Verificar si podemos agregar al grupo actual (misma fecha, serie, cond)
                if (
                    $grupoActual['fecha'] === $fila['fecha'] &&
                    $grupoActual['serie'] === $fila['serie']
                ) {
                    // Agregar al grupo actual
                    $grupoActual['monto'] += $fila['monto'];
                    $grupoActual['subtotal'] += $fila['valor_venta'];
                    $grupoActual['total_igv'] += $fila['igv'];
                    $grupoActual['total_icbper'] += $fila['icbper'];
                    $grupoActual['nums'][] = $fila['numero'];
                } else {
                    // No se puede agregar, cerrar grupo actual y empezar nuevo
                    $maqueta[] =  $this->finalizarGrupo($grupoActual);
                    $grupoActual = [
                        'fecha' => $fila['fecha'],
                        'serie' => $fila['serie'],
                        'nums' => [$fila['numero']],
                        'cond' => 'A',
                        'monto' => $fila['monto'],
                        'num_clie' => $fila['ruc'],
                        'nombre_clien' => $fila['razon_social'],
                        'subtotal' => $fila['valor_venta'],
                        'total_igv' => $fila['igv'],
                        'total_icbper' => $fila['icbper']
                    ];
                }
            }

            // Agregar el último grupo si existe
            if ($grupoActual !== null) {
                $maqueta[] = $this->finalizarGrupo($grupoActual);
            }
        }

        $row = 2;

        foreach ($maqueta as $key => $value) {
            $myDateTime = DateTime::createFromFormat('Y-m-d', $value["fecha"]);
            $dat = (string) $myDateTime->format('d/m/Y');

            $sheet->setCellValue('A' . $row, $dat);
            $sheet->setCellValue('B' . $row, $value["tipo_moneda"]);
            $sheet->setCellValue('C' . $row, $value['documento']);
            $sheet->setCellValue('D' . $row, $value['numero_documento']);
            $sheet->setCellValue('E' . $row, 'A');
            $sheet->setCellValue('F' . $row, $value['ruc']);
            $sheet->setCellValue('G' . $row, $value['razon_social']);
            $sheet->setCellValue('H' . $row, $value['vvventa']);
            $sheet->setCellValue('I' . $row, $value['valor_venta']);
            $sheet->setCellValue('J' . $row, $value['igv']);
            $sheet->setCellValue('K' . $row, $value['icbper']);
            $sheet->setCellValue('L' . $row, $value['total']);
            $sheet->setCellValue('M' . $row, $value['tipo_cambio']);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'maqueta_venta_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filePath = WRITEPATH . 'temp/' . $filename;

        // Crear directorio si no existe
        if (!is_dir(WRITEPATH . 'temp')) {
            mkdir(WRITEPATH . 'temp', 0755, true);
        }

        $writer->save($filePath);

        return $filename;
    }

    public function finalizarGrupo($grupo)
    {
        $result = array(
            "fecha" => $grupo['fecha'],
            "tipo_moneda" => 'S',
            "documento" => "BOLETA DE VENTA ELECTRONICA",
            "condicion" => 'A',
            "vvventa" => $grupo['subtotal'],
            "valor_venta" => $grupo['subtotal'],
            "igv" => $grupo['total_igv'],
            "icbper" => $grupo['total_icbper'],
            "total" => $grupo['monto'],
            "tipo_cambio" => 1
        );

        if (count($grupo['nums']) > 1) {
            $result['ruc'] = "00000001";
            $result['razon_social'] = "CLIENTES VARIOS";
            $result['numero_documento'] = $grupo['serie'] . "-" . $grupo['nums'][0] . '/' . end($grupo['nums']);
        } else {
            if ($grupo['monto'] < 700) {
                $result['ruc'] = "00000001";
                $result['razon_social'] = "CLIENTES VARIOS";
            } else {
                $result['ruc'] = $grupo['num_clie'];
                $result['razon_social'] = $grupo['nombre_clien'];
            }

            $result['numero_documento'] = $grupo['serie'] . "-" . $grupo['nums'][0];
        }

        return $result;
    }

    public function agregarFila($fila)
    {
        $add = array(
            "fecha" => $fila['fecha'],
            "tipo_moneda" => 'S',
            "documento" => $fila['comprobante_tipo'],
            "numero_documento" => $fila['serie'] . "-" . $fila['numero'],
            "condicion" => 'A',
            "ruc" => $fila['ruc'],
            "razon_social" => $fila['razon_social'],
            "vvventa" => $fila['valor_venta'],
            "valor_venta" => $fila['valor_venta'],
            "igv" => $fila['igv'],
            "icbper" => $fila['icbper'],
            "total" => $fila['monto'],
            "tipo_cambio" => 1
        );

        return $add;
    }

    public function saveComprobantesMaquetas($data, $file)
    {
        $migracion = new MigracionModel();
        $migrar = new MigrarModel();

        $fecha = strtoupper(trim($data['fecha']));
        $serie = strtoupper(trim($data['serie']));
        $numero = strtoupper(trim($data['numero']));
        $monto = strtoupper(trim($data['monto']));
        $ruc = strtoupper(trim($data['ruc']));
        $tipo = strtoupper(trim($data['tipo']));
        $razon_social = strtoupper(trim($data['razon_social']));
        $igv = $data['igv'];
        $numero_ruc = $data['numero_ruc'];
        $icbper = $data['icbper'];

        $fileExcel = $file;

        $dataMigracion = array("nombre_archivo" => "archivo");
        $migracion->insert($dataMigracion);

        $id_migracion = $migracion->getInsertID();

        $ruta = $fileExcel->getTempName(); // ruta temporal del archivo

        // Cargar el archivo Excel
        $spreadsheet = IOFactory::load($ruta);
        $hoja = $spreadsheet->getActiveSheet(); // También puedes usar getSheet(n) para otra hoja

        // Leer todas las filas
        foreach ($hoja->getRowIterator() as $fila) {
            $index = $fila->getRowIndex();

            $fechaExcel = $hoja->getCell($fecha . $index)->getValue();

            if (is_numeric($fechaExcel)) {
                // 🟢 Caso: número de serie de Excel
                $fechaMigrar = Date::excelToDateTimeObject($fechaExcel)->format('Y-m-d');
            } else {
                // 🔵 Caso: texto — intentamos detectar el formato
                $valor = str_replace(['.', '-'], '/', $fechaExcel); // uniformiza separadores

                // Intentar con formato común
                $fechaObj = DateTime::createFromFormat('d/m/Y', $valor);

                if (!$fechaObj) {
                    // Intentar con formato alternativo
                    $fechaObj = DateTime::createFromFormat('Y-m-d', $valor);
                }

                if ($fechaObj) {
                    $fechaMigrar = $fechaObj->format('Y-m-d');
                } else {
                    $fechaMigrar = null; // o manejar error
                }
            }

            $serieMigrar = $hoja->getCell($serie . $index)->getValue();
            $numeroMigrar = $hoja->getCell($numero . $index)->getValue();
            $montoMigrar = $hoja->getCell($monto . $index)->getValue();
            $rucMigrar = $hoja->getCell($ruc . $index)->getValue();
            $tipoMigrar = $hoja->getCell($tipo . $index)->getValue();
            $razon_socialMigrar = $hoja->getCell($razon_social . $index)->getValue();
            $icbperMigrar = $hoja->getCell($icbper . $index)->getValue();

            if ($numero_ruc == 1 || $numero_ruc == '00000001' || $numero_ruc == "" || $numero_ruc == "-") {
                $ruc_dni = "00000001";
                $razon_socialMigrar = "CLIENTES VARIOS";
            } else {

                if ((strlen($rucMigrar) == 8 || strlen($rucMigrar) == 11) && $razon_socialMigrar !== "") {
                    $ruc_dni = $rucMigrar;
                    $razon_socialMigrar = $razon_socialMigrar;
                } else {
                    $ruc_dni = "00000001";
                    $razon_socialMigrar = "CLIENTES VARIOS";
                }

                /*if ($razon_socialMigrar == "") {
                    $consulta_ruc = $rucTable->find($rucMigrar);

                    if ($consulta_ruc) {
                        $ruc_dni = $consulta_ruc['id_ruc'];
                        $razon_socialMigrar = $consulta_ruc['ruc_razon_social'];
                    } else {
                        $ruc_dni = $rucMigrar;
                        $razon_socialMigrar = $this->buscar_razon_social($rucMigrar);
                    }
                }*/
            }

            if ($tipoMigrar == "01") {
                $comprobante_tipo = "FACTURA";
            } else if ($tipoMigrar == "03") {
                $comprobante_tipo = "BOLETA DE VENTA";
            } else if ($tipoMigrar == "07") {
                $comprobante_tipo = "NOTA DE CREDITO";
            } else if ($tipoMigrar == "08") {
                $comprobante_tipo = "NOTA DE DEBITO";
            } else if ($tipoMigrar == "09") {
                $comprobante_tipo = "GUIA DE REMISION";
            } else {
                $comprobante_tipo = "OTRO DOCUMENTO";
            }

            if ($igv == 1) {
                $subtotal = $montoMigrar / 1.18 - $icbperMigrar;
                $total_igv = $montoMigrar - $subtotal;
            } else {
                $subtotal = $montoMigrar - $icbperMigrar;
                $total_igv = 0;
            }

            $dataMigrar = array(
                "id_migracion" => $id_migracion,
                "fecha" => $fechaMigrar,
                "serie" => $serieMigrar,
                "numero" => $numeroMigrar,
                "valor_venta" => $subtotal,
                "igv" => $total_igv,
                "icbper" => $icbperMigrar,
                "monto" => $montoMigrar,
                "ruc" => $rucMigrar,
                "tipo" => $tipoMigrar,
                "comprobante_tipo" => $comprobante_tipo,
                "ruc_empresa" => $ruc_dni,
                "razon_social" => $razon_socialMigrar
            );

            $migrar->insert($dataMigrar);
        }

        return $id_migracion;
    }

    public function importarBoletas()
    {
        set_time_limit(300); // 5 minutos
        ini_set('max_execution_time', 300);

        $migrar = new MigrarModel();
        $migracion = new MigracionModel();

        try {
            $data = $this->request->getPost();

            $file = $this->request->getFile('fileExcel');

            if (!$file || !$file->isValid()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se ha seleccionado un archivo válido.']);
            }

            //save comprobantes
            $id_migracion = $this->saveComprobantesMaquetas($data, $file);

            // Generar Excel
            $filename = $this->generateExcel($id_migracion);

            //$migrar->where('id_migracion', $id_migracion)->delete();

            //$migracion->delete($id_migracion);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Archivo generado exitosamente',
                'downloadUrl' => site_url("excel/download/$filename"),
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }

    public function descargarExcelComprobantes()
    {

        $comprobante = new ComprobanteModel();

        $ruc = $this->request->getPost('ruc');
        $minimo = $this->request->getPost('minimo');
        $maximo = $this->request->getPost('maximo');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'FECHA');
        $sheet->setCellValue('B1', 'TIPO_MONEDA');
        $sheet->setCellValue('C1', 'DOCUMENTO');
        $sheet->setCellValue('D1', '#_DOCUMENTO');
        $sheet->setCellValue('E1', 'CONDICION');
        $sheet->setCellValue('F1', 'RUC');
        $sheet->setCellValue('G1', 'RAZON_SOCIAL');
        $sheet->setCellValue('H1', 'VVENTA');
        $sheet->setCellValue('I1', 'VALOR_DE_VENTA');
        $sheet->setCellValue('J1', 'IGV');
        $sheet->setCellValue('K1', 'TOTAL');
        $sheet->setCellValue('L1', 'TIPO_CAMBIO');

        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        $datos = $comprobante->query("select * from comprobante,tipo_comprobante,tipo_moneda
        where  comprobante.id_tipo_comprobante=tipo_comprobante.id_tipo_comprobante and tipo_moneda.id_tipo_moneda=comprobante.id_tipo_moneda
        and comprobante.comprobante_tipo_estado=1 and comprobante.ruc_empresa_numero=" . $ruc . "
        and comprobante.comprobante_fecha BETWEEN '" . $minimo . "' and '" . $maximo . "' order by tipo_comprobante.id_tipo_comprobante desc,
        comprobante.comprobante_documento_serie_caracteristicas asc
        ,comprobante.comprobante_fecha asc, comprobante.comprobante_documento_serie_numero asc")->getResult();

        foreach ($datos as $key => $value) {
            $sheet->setCellValue('A' . ($key + 2), $value->comprobante_fecha);
            $sheet->setCellValue('B' . ($key + 2), $value->tipo_moneda_descripcion);
            $sheet->setCellValue('C' . ($key + 2), $value->tipo_comprobante_descripcion);
            $sheet->setCellValue('D' . ($key + 2), $value->comprobante_documento_serie_numero);
            $sheet->setCellValue('E' . ($key + 2), $value->comprobante_condicion);
            $sheet->setCellValue('F' . ($key + 2), $value->comprobante_ruc);
            $sheet->setCellValue('G' . ($key + 2), $value->comprobante_nombre_razon);
            $sheet->setCellValue('H' . ($key + 2), $value->comprobante_venta);
            $sheet->setCellValue('I' . ($key + 2), $value->comprobante_venta);
            $sheet->setCellValue('J' . ($key + 2), 0.00);
            $sheet->setCellValue('K' . ($key + 2), $value->comprobante_venta);
            $sheet->setCellValue('L' . ($key + 2), $value->comprobante_tipo_cambio);
        }

        // Configurar cabeceras para la descarga
        $filename = 'hello_world.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function vacearBoletas()
    {
        $ayuda = new AyudaBoletaModel();
        $comprobante = new ComprobanteModel();

        try {
            $inicio = $this->request->getPost('inicio');
            $fin = $this->request->getPost('fin');
            $numero_ruc = $this->request->getPost('numero_ruc');

            $comprobante->where("ruc_empresa_numero", $numero_ruc)->where("comprobante_fecha >=", $inicio)->where("comprobante_fecha <=", $fin)->delete();

            $ayuda->where("id_ruc_empresa", $numero_ruc)->where("fecha >=", $inicio)->where("fecha <=", $fin)->delete();

            return $this->response->setJSON(['status' => 'success', 'message' => "Datos eliminados correctamente."]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function verAcceso($id)
    {
        $contrib = new ContribuyenteModel();

        $data_contrib = $contrib->select('ruc, razon_social, acceso')->find($id);

        return $this->response->setJSON(['status' => 'success', 'datos' => $data_contrib]);
    }

    public function updatePassword()
    {
        $contrib = new ContribuyenteModel();

        try {
            $id = $this->request->getPost('idcon');
            $password = $this->request->getPost('clave');

            $contrib->update($id, ['acceso' => $password]);

            return $this->response->setJSON(['status' => 'success', 'message' => "Contraseña actualizada correctamente."]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function showContratos($id)
    {
        $contrato = new ContratosModel();

        $contratos = $contrato->query("SELECT ct.razon_social, c.file, c.id FROM contratos c INNER JOIN contribuyentes ct ON ct.id = c.contribuyenteId WHERE c.contribuyenteId = $id AND c.estado != 0 ORDER BY c.id DESC")->getResultArray();

        return $this->response->setJSON(['status' => 'success', 'datos' => $contratos]);
    }

    public function agregarContrato()
    {
        $contrato = new ContratosModel();
        $tarifa = new HistorialTarifaModel();

        try {
            $id = $this->request->getPost('id_emp');
            $file = $this->request->getFile('fileContrato');

            $consulta = $contrato->query("SELECT c.id, c.file, ct.ruc FROM contratos c INNER JOIN contribuyentes ct ON ct.id = c.contribuyenteId WHERE c.contribuyenteId = $id AND c.estado = 1")->getRowArray();

            $ext_contrato = $file->getExtension();
            $codigo = str_pad(mt_rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);

            $archivo_contrato = "CONTRATO_" . $consulta['ruc'] . "_" . $codigo . "." . $ext_contrato;

            $file->move(FCPATH . 'contratos', $archivo_contrato);

            if ($consulta['file'] == "") {
                $contrato->update($consulta["id"], ["file" => $archivo_contrato]);
            } else {

                $contrato->update($consulta["id"], ["estado" => 2]);

                $data = [
                    "contribuyenteId" => $id,
                    "file" => $archivo_contrato,
                    "fechaInicio" => date("Y-m-d"),
                    "estado" => 1
                ];

                $contrato->insert($data);

                $idContrato = $contrato->getInsertID();
                $fechaInicio = date("Y-m-d");

                $idc = $consulta["id"];

                $dataTarifa = $tarifa->query("SELECT * FROM historial_tarifas WHERE estado = 1 AND contratoId = $idc")->getRowArray();

                $tarifa->insert([
                    'contratoId' => $idContrato,
                    'fecha_inicio' => $fechaInicio,
                    'monto_mensual' => $dataTarifa['monto_mensual'],
                    'monto_anual' => $dataTarifa['monto_anual'],
                    'estado' => 1
                ]);
            }

            return $this->response->setJSON(['status' => 'success', 'message' => "Contrato agregado correctamente."]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function datos($monto, $razon_social, $tipo_comprobante, $ruc)
    {
        $rucFind = new RucModel();
        $codificacion = new CodificacionModel();

        if ((float) $monto != 0) {

            if ($tipo_comprobante == 2 || $tipo_comprobante == 4) {

                $c = 0;

                $resultado = $rucFind->find($ruc);

                if ($resultado) {
                    $c = 1;
                    $nombre_razon = $resultado["ruc_razon_social"];
                    $codigo = $ruc;
                }

                if ($c == 0) {

                    $codigo = $ruc;
                    $nombre_razon = $razon_social;

                    $data_sunat = $this->buscar_razon_social($codigo);

                    $nombre_razon = $data_sunat;

                    $data_insert = [
                        "id_ruc" => $codigo,
                        "ruc_razon_social" => $nombre_razon,
                        "ruc_estado" => 1,
                    ];

                    $rucFind->insert($data_insert);
                } else {

                    if ($nombre_razon == "******" || $nombre_razon == "") {
                        $nombres_ = $this->buscar_razon_social($codigo);

                        $data_update = [
                            "ruc_razon_social" => $nombres_,
                            "ruc_estado" => 1,
                        ];

                        $rucFind->update($codigo, $data_update);

                        $nombre_razon = $nombres_;
                    }
                }
            } else {

                if ($razon_social != "") {
                    $codigo = $ruc;
                    $nombre_razon = $razon_social;
                } else {

                    $dat = $codificacion->query("select * from codificacion,codigo_tipo where codificacion.id_codigo_tipo=codigo_tipo.id_codigo_tipo and codificacion.id_codigo_tipo=2 and codificacion.ruc_empresa_numero= '$ruc' and codificacion.id_tipo_comprobante = $tipo_comprobante")->getResultArray();

                    if ($dat) {
                        $codigo = $dat['codificacion_numero'];
                        $nombre_razon = $dat['codigo_tipo_descripcion'];
                    }
                }
            }
        } else {
            $dat = $codificacion->query("select * from codificacion,codigo_tipo where codificacion.id_codigo_tipo=codigo_tipo.id_codigo_tipo and codificacion.id_codigo_tipo=1 and codificacion.ruc_empresa_numero='$ruc' and codificacion.id_tipo_comprobante = $tipo_comprobante")->getResultArray();

            if ($dat) {
                $codigo = $dat['codificacion_numero'];
                $nombre_razon = $dat['codigo_tipo_descripcion'];
            }
        }

        return array($codigo, $nombre_razon);
    }

    public function buscar_razon_social($ruc)
    {
        $ruta = "https://facturalahoy.com/api/empresa/$ruc/facturalaya_erickpeso_05jFE7sAOudi8j0/completa";

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $ruta,
            CURLOPT_USERAGENT => 'Consulta Datos',
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_TIMEOUT => 400,
            CURLOPT_FAILONERROR => true
        ));
        $respuesta = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($respuesta, true);

        if ($response['respuesta'] == "error") {
            return "";
        } else {
            return $response['razon_social'];
        }
    }
}
