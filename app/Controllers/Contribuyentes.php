<?php

namespace App\Controllers;

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

        $certi = new CertificadoDigitalModel();

        $consulta_certificado_por_vencer = $certi->query('SELECT c.ruc, c.razon_social, cd.tipo_certificado, cd.fecha_inicio, cd.fecha_vencimiento
        FROM certificado_digital cd
        inner join contribuyentes c on c.id = cd.contribuyente_id
        WHERE cd.fecha_vencimiento BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY) and cd.estado = 1;')->getResult();

        $menu = $this->permisos_menu();

        return view('contribuyente/lista', compact('sistemas', 'consulta_certificado_por_vencer', 'menu'));
    }

    public function getIdContribuyente($id)
    {
        $cont = new ContribuyenteModel();

        $data = $cont->select('id, ruc, razon_social')->find($id);

        return $this->response->setJSON($data);
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

    public function listaContribuyentes($filtro)
    {
        $model = new ContribuyenteModel();
        $confiNoti = new ConfiguracionNotificacionModel();
        $declaracionSunat = new DeclaracionSunatModel();
        $tributo = new TributoModel();
        $uit_ = new UitModel();

        $sql = "";
        $asig = "";

        if ($filtro !== 'TODOS') {
            $sql = "AND c.tipoServicio = '$filtro'";
        }

        if (session()->perfil_id > 2) {
            $asig = " AND cu.usuario_id = " . session()->id;
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
        FROM contribuyentes c left join contribuyentes_usuario cu ON cu.contribuyente_id = c.id WHERE c.estado > 0 $sql $asig order by c.id desc")->getResult();

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
                $value->respuesta = 0;
                $value->tipo = "Falta configuración";
            }
        }

        return $this->response->setJSON($data);
    }

    public function guardar()
    {
        $sistema = new SistemaContribuyenteModel();
        $model = new ContribuyenteModel();
        $codificacion = new CodificacionModel();

        $model->db->transStart();

        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

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
                'fechaContrato' => $data['fechaContrato'],
                'telefono' => "",
                'correo' => "",
                'usuario_secundario' => "",
                'clave_usuario_secundario' => "",
                'acceso' => $data['numeroDocumento'],
                'estado' => 1
            ];

            $clientesVarios = $data['clientesVarios'];
            $boletaAnulado = $data['boletaAnulado'];
            $facturaAnulado = $data['facturaAnulado'];

            $tarifa = new HistorialTarifaModel();

            if ($idTabla === "0") {
                if ($verificar) {
                    return $this->response->setJSON(['status' => 'error', 'message' => "El RUC ya se encuentra registrado."]);
                }

                $model->insert($datos);

                $contribuyente_id = $model->insertID();

                $model->update($contribuyente_id, ['user_add' => session()->id]);

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

                $tarifa->insert([
                    'contribuyente_id' => $contribuyente_id,
                    'fecha_inicio' => $fechaInicio,
                    'monto_mensual' => $data['costoMensual'],
                    'monto_anual' => $data['costoAnual'],
                    'estado' => 1
                ]);

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

                $tarifaData = $tarifa->where('contribuyente_id', $idTabla)->orderBy('id', 'asc')->first();

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

        $data_tarifa = $tarifa->where('contribuyente_id', $id)->where('estado', 1)->orderBy('id', 'desc')->findAll();
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

            $last_tarifa = $tarifa->where('contribuyente_id', $data['idTableTarifa'])->where('estado', 1)->orderBy('fecha_inicio', 'DESC')->first();

            if ($data['fechaInicioTarifa'] <= $last_tarifa['fecha_inicio']) {
                return $this->response->setJSON(['status' => 'error', 'message' => "No puedes colocar una fecha menor o igual a la ultima fecha de la tarifa"]);
            }

            if ($last_tarifa) {
                $tarifa->update($last_tarifa['id'], ['fecha_fin' => $data['fechaInicioTarifa']]);
            }

            $tarifa->insert([
                'contribuyente_id' => $data['idTableTarifa'],
                'fecha_inicio' => $data['fechaInicioTarifa'],
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

    public function listaHonorariosCobros($select)
    {
        $model = new ContribuyenteModel();
        $pago = new PagosModel();

        $sql = "";

        if ($select !== 'TODOS') {
            $sql = "WHERE c.tipoServicio = '$select'";
        }

        $datos = $model->query("SELECT c.id, c.razon_social, c.ruc, c.tipoPago, c.diaCobro, c.tipoServicio, c.tipoSuscripcion FROM contribuyentes $sql as c ORDER BY c.id desc")->getResult();

        foreach ($datos as $key => $value) {
            $id = $value->id;

            if ($value->tipoPago == 'ADELANTADO') {
                $maxPago = $pago->query("SELECT contribuyente_id, MAX(mesCorrespondiente) as ultimoMes FROM pagos WHERE contribuyente_id = $id GROUP BY contribuyente_id")->getRow();

                if (!$maxPago) {
                    $debe = "1 mes";
                } else {
                    // Fecha de primer pago
                    $primerPago = new DateTime($maxPago->ultimoMes);

                    $fechaActual = date('Y-m-d');
                    // Fecha actual
                    $fechaActual = new DateTime($fechaActual);

                    $siguientePago = clone $primerPago;
                    $mesesDebe = 0;

                    // Calcular el siguiente día de pago hasta que supere la fecha actual
                    while ($siguientePago <= $fechaActual) {
                        $siguientePago->modify('+1 month');
                        $diaOriginal = (int)$primerPago->format('d');
                        $diaActual = (int)$siguientePago->format('d');

                        // Ajustar para meses con menos días (como febrero)
                        if ($diaActual < $diaOriginal) {
                            $siguientePago->modify('last day of previous month');
                        }

                        // Si el siguiente pago es menor o igual a la fecha actual, incrementar meses debe
                        if ($siguientePago <= $fechaActual) {
                            $mesesDebe++;
                        }
                    }

                    $debe = $mesesDebe;
                }
            } else {
                $debe = "0";
            }

            $value->debe = $debe;
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

        $data = array('estado' => $status);

        $model->update($id, $data);

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

    public function importarBoletas()
    {
        try {
            $file = $this->request->getFile('fileExcel');
            $file->move(WRITEPATH . 'uploads/boletas/');
            $filePath = WRITEPATH . 'uploads/boletas/' . $file->getName();
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /*public function migrarContribuyentes()
    {
        $empresa = new RucEmpresaModel();
        $cont = new ContribuyenteModel();
        $tarifa = new HistorialTarifaModel();

        $data = $empresa->findAll();

        foreach ($data as $key => $value) {

            if($value["gratuito"] === "NO") {
                $tipoSuscripcion = "NO GRATUITO";
            } else {
                $tipoSuscripcion = "GRATUITO";
            }

            if($value["tipo_de_pago"] === 'ADELANTADO') {
                $tipoPago = "ADELANTADO";
            } else {
                $tipoPago = "ATRASADO";
            }

            if($value['ruc_empresa_monto'] != null) {
                $montoMensual = $value['ruc_empresa_monto'];
            } else {
                $montoMensual = 0;
            }

            if($value['costo_anual'] != null) {
                $montoAnual = $value['costo_anual'];
            } else {
                $montoAnual = 0;
            }

            if($value['tipo_servicio'] != null) {
                $tipoServicio = $value['tipo_servicio'];
            } else {
                $tipoServicio = "CONTABLE";
            }

            $datos = array(
                "ruc" => $value["ruc_empresa_numero"],
                "razon_social" => $value["ruc_empresa_razon_social"],
                "nombre_comercial" => "",
                "direccion_fiscal" => "",
                "ubigeo_id" => '220901',
                "urbanizacion" => "",
                "tipoSuscripcion" => $tipoSuscripcion,
                "tipoServicio" => $tipoServicio,
                "tipoPago" => $tipoPago,
                "costoMensual" => $montoMensual,
                "costoAnual" => $value["costo_anual"],
                "diaCobro" => 1,
                "fechaContrato" => date('Y-m-d'),
                "telefono" => "",
                "access" => $value["ruc_empresa_numero"],
                "user_add" => session()->id,
                "estado" => $value['ruc_empresa_estado']
            );

            $cont->insert($datos);

            $contribuyente_id = $cont->insertID();

            $tarifa->insert([
                'contribuyente_id' => $contribuyente_id,
                'fecha_inicio' => date('Y-m')."-01",
                'monto_mensual' => $montoMensual,
                'monto_anual' => $montoAnual,
                'estado' => 1
            ]);

            echo "<pre>"; print_r($datos); echo "</pre> <br>";
        }
    }*/
}
