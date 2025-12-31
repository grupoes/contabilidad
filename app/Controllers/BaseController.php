<?php

namespace App\Controllers;

use App\Models\AfpModel;
use App\Models\AnioModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\SesionCajaModel;
use App\Models\HistorialTarifaModel;
use App\Models\PagosModel;
use App\Models\ContribuyenteModel;
use App\Models\PermisosModel;
use App\Models\MovimientoModel;
use App\Models\PagoServidorModel;
use App\Models\SedeModel;
use App\Models\SedeCajaModel;
use App\Models\UserModel;
use App\Models\CertificadoDigitalModel;
use App\Models\FechaDeclaracionModel;
use App\Models\PagoAnualModel;
use App\Models\PdtAnualModel;
use App\Models\PdtPlameModel;
use App\Models\PdtRentaModel;
use App\Models\ServidorModel;
use App\Models\SireModel;
use App\Models\SistemaModel;
use DateTime;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = \Config\Services::session();
    }

    public function idSesionCaja($tipoPago)
    {
        $sesion = new SesionCajaModel();

        $idUser = session()->id;

        if ($tipoPago == 1) {
            $idcaja = 1;
        } else {
            $idcaja = 2;
        }

        $sesions = $sesion->join('sede_caja', 'sede_caja.id_sede_caja = sesion_caja.id_sede_caja')->where('sesion_caja.id_usuario', $idUser)->where('sede_caja.id_caja', $idcaja)->orderBy('sesion_caja.id_sesion_caja', 'DESC')->first();

        return $sesions['id_sesion_caja'];
    }

    public function getMontoMensual($idContribuyente)
    {
        $historial = new HistorialTarifaModel();

        $pago = new PagosModel();
        $contrib = new ContribuyenteModel();

        $dataContrib = $contrib->where('id', $idContribuyente)->first();

        $getUltimoPago = $pago->where('contribuyente_id', $idContribuyente)->orderBy('id', 'DESC')->first();

        if (!$getUltimoPago) {
            $fechaContratoObj = new DateTime($dataContrib['fechaContrato']);
            $fechaPago = $fechaContratoObj->format('Y-m') . "-" . $dataContrib['diaCobro'];

            $diaVence = $fechaPago;
        } else {
            $fecha = new DateTime($getUltimoPago['mesCorrespondiente']); // Fecha inicial
            $fecha->modify('+1 month'); // Sumar un mes
            $diaVence = $fecha->format('Y-m-d');
        }

        $getHistorial = $historial->where('contribuyente_id', $idContribuyente)->where('fecha_inicio <=', $diaVence)->where('estado', 1)->orderBy('id', 'DESC')->first();

        $monto = $getHistorial['monto_mensual'];

        return $monto;
    }

    public function permisos_menu()
    {
        $permisos = new PermisosModel();

        $modulos = $permisos->select('modulos.modulo_padre,(SELECT m2.nombre FROM modulos m2 WHERE m2.id = modulos.modulo_padre) AS modulo_padre_nombre, (SELECT m2.icono FROM modulos m2 WHERE m2.id = modulos.modulo_padre) AS modulo_padre_icono')
            ->join('modulos', 'modulos.id = permisos.modulo_id')
            ->where('permisos.perfil_id', session()->perfil_id)
            ->where('modulos.estado', 1)
            ->groupBy('modulos.modulo_padre')
            ->findAll();

        foreach ($modulos as $key => $value) {
            $hijos = $permisos->select('modulos.id,modulos.nombre,modulos.url, modulos.orden')
                ->join('modulos', 'modulos.id = permisos.modulo_id')
                ->where('permisos.perfil_id', session()->perfil_id)
                ->where('modulos.modulo_padre', $value['modulo_padre'])
                ->where('modulos.estado', 1)
                ->groupBy('modulos.id')
                ->orderBy('modulos.orden', 'ASC')
                ->findAll();

            $modulos[$key]['hijos'] = $hijos;
        }

        return $modulos;
    }

    public function generarMovimiento($sesionCaja, $concepto, $formaPago, $metodoPago, $monto, $descripcion, $tipoComprobante, $descripcionComprobante, $estado, $fecha_pago, $vaucher, $idUser)
    {
        $mov = new MovimientoModel();

        $mov->insert([
            'id_sesion_caja' => $sesionCaja,
            'mov_formapago' => $formaPago,
            'id_metodo_pago' => $metodoPago,
            'mov_concepto' => $concepto,
            'mov_monto' => $monto,
            'mov_descripcion' => $descripcion,
            'id_tipo_comprobante' => $tipoComprobante,
            'tipo_comprobante_descripcion' => $descripcionComprobante,
            'mov_fecha' => date('Y-m-d'),
            'mov_fecha_pago' => $fecha_pago,
            'mov_hora' => date('H:i:s'),
            'mov_estado' => $estado,
            'mov_cobro' => 0,
            'userRegister' => $idUser,
            'nombreUser' => session()->nombre . ' ' . session()->apellidos,
            'vaucher' => $vaucher,
        ]);

        $id = $mov->insertID();

        return $id;
    }

    public function obtenerCajaSedeVirtual()
    {
        $sede = new SedeModel();
        $user = new UserModel();

        $sedeCaja = $sede->where('caja_virtual', 1)->first();

        $idsede = $sedeCaja['id'];

        $usuario = $user->where('perfil_id', 3)->where('sede_id', $idsede)->where('estado', 1)->first();

        return [
            'idUser' => $usuario['id'],
            'idSede' => $idsede,
        ];
    }

    public function Aperturar($metodoPago, $idsede = null)
    {
        $sesion = new SesionCajaModel();
        $sedeCaja = new SedeCajaModel();
        $user = new UserModel();

        $sesion->db->transStart();

        try {

            if ($metodoPago == "1") {
                $sede = $idsede;
                $usuario = $user->where('perfil_id', 3)->where('sede_id', $sede)->where('estado', 1)->first();
                $idUser = $usuario['id'];
            } else {
                $datos = $this->obtenerCajaSedeVirtual();

                $idUser = $datos['idUser'];
                $sede = $datos['idSede'];
            }

            $sesions = $sesion->where('id_usuario', $idUser)->orderBy('id_sesion_caja', 'DESC')->findAll(2);

            $getSedeCajaFisica = $sedeCaja->where('id_sede', $sede)->where('id_caja', 1)->first();
            $getSedeCajaVirtual = $sedeCaja->where('id_sede', $sede)->where('id_caja', 2)->first();

            if (!$getSedeCajaFisica || !$getSedeCajaVirtual) {
                throw new \Exception("No se encontraron las configuraciones de caja.");
            }

            $fecha_apertura = date('Y-m-d H:i:s');

            if ($sesions) {

                $spertura = date('Y-m-d', strtotime($sesions[0]['ses_fechaapertura']));

                if ($sesions[0]['ses_estado'] == 1) {

                    if ($spertura !== date('Y-m-d')) {
                        foreach ($sesions as $key => $value) {
                            $data_update = array(
                                "ses_montocierre" => 0,
                                "ses_estado" => 0,
                                "ses_fechacierre" => date('Y-m-d H:i:s')
                            );

                            $sesion->update($value['id_sesion_caja'], $data_update);
                        }

                        $datos_fisica = array(
                            "id_usuario" => $idUser,
                            "id_sede_caja" => $getSedeCajaFisica['id_sede_caja'],
                            "ses_fechaapertura" => $fecha_apertura,
                            "ses_montoapertura" => $getSedeCajaFisica['sede_caja_monto'],
                            "ses_montocierre" => 0,
                            "ses_estado" => 1,
                            "ses_fechacierre" => ""
                        );

                        $sesion->insert($datos_fisica);

                        $idSesionFisica = $sesion->insertID();

                        $datos_virtual = array(
                            "id_usuario" => $idUser,
                            "id_sede_caja" => $getSedeCajaVirtual['id_sede_caja'],
                            "ses_fechaapertura" => $fecha_apertura,
                            "ses_montoapertura" => $getSedeCajaVirtual['sede_caja_monto'],
                            "ses_montocierre" => 0,
                            "ses_estado" => 1,
                            "ses_fechacierre" => ""
                        );

                        $sesion->insert($datos_virtual);

                        $idSesionVirtual = $sesion->insertID();
                    } else {
                        $sesion->db->transComplete();

                        if ($sesion->db->transStatus() === false) {
                            throw new \Exception("Error al realizar la operación.");
                        }
                        return [
                            "idSesionFisica" => $sesions[1]['id_sesion_caja'],
                            "idSesionVirtual" => $sesions[0]['id_sesion_caja'],
                            "idUser" => $idUser,
                            "status" => "success",
                        ];
                    }
                }
            } else {
                $datos_fisica = array(
                    "id_usuario" => $idUser,
                    "id_sede_caja" => $getSedeCajaFisica['id_sede_caja'],
                    "ses_fechaapertura" => $fecha_apertura,
                    "ses_montoapertura" => $getSedeCajaFisica['sede_caja_monto'],
                    "ses_montocierre" => 0,
                    "ses_estado" => 1,
                    "ses_fechacierre" => ""
                );

                $sesion->insert($datos_fisica);

                $idSesionFisica = $sesion->insertID();

                $datos_virtual = array(
                    "id_usuario" => $idUser,
                    "id_sede_caja" => $getSedeCajaVirtual['id_sede_caja'],
                    "ses_fechaapertura" => $fecha_apertura,
                    "ses_montoapertura" => $getSedeCajaVirtual['sede_caja_monto'],
                    "ses_montocierre" => 0,
                    "ses_estado" => 1,
                    "ses_fechacierre" => ""
                );

                $sesion->insert($datos_virtual);

                $idSesionVirtual = $sesion->insertID();
            }

            $sesion->db->transComplete();

            if ($sesion->db->transStatus() === false) {
                throw new \Exception("Error al realizar la operación.");
            }

            return [
                "idSesionFisica" => $idSesionFisica,
                "idSesionVirtual" => $idSesionVirtual,
                "idUser" => $idUser,
                "status" => "success",
            ];
        } catch (\Exception $e) {
            $sesion->db->transRollback(); // Revertir la transacción
            return [
                "status" => "error",
            ];
        }
    }

    public function deletePagoArray($contribId, $monto)
    {
        $pago = new PagosModel();

        $dataPago = $pago->where('contribuyente_id', $contribId)->where('estado !=', 'eliminado')->orderBy('id', 'DESC')->findAll();

        $montoRestante = $monto;

        foreach ($dataPago as $key => $value) {
            if ($montoRestante <= 0) {
                break;
            }

            $montoPagado = $value['montoPagado'];
            $montoTotal = $value['monto_total'];

            if ($montoRestante >= $montoPagado) {
                $pago->update($value['id'], ['estado' => 'eliminado']);
                $montoRestante -= $montoPagado;
            } else {
                $nuevoMontoPagado = $montoPagado - $montoRestante;
                $nuevoMontoPendiente = $montoTotal - $nuevoMontoPagado;

                $pago->update($value['id'], [
                    'montoPagado' => $nuevoMontoPagado,
                    'montoPendiente' => $nuevoMontoPendiente,
                    'estado' => 'pendiente'
                ]);

                $montoRestante = 0;
            }
        }
    }

    public function deletePagoServidorArray($contribId, $monto)
    {
        $pago = new PagoServidorModel();

        $dataPago = $pago->where('contribuyente_id', $contribId)->where('estado !=', 'eliminado')->orderBy('id', 'DESC')->findAll();

        $montoRestante = $monto;

        foreach ($dataPago as $key => $value) {
            if ($montoRestante <= 0) {
                break;
            }

            $montoPagado = $value['monto_pagado'];
            $montoTotal = $value['monto_total'];

            if ($montoRestante >= $montoPagado) {
                $pago->update($value['id'], ['estado' => 'eliminado']);
                $montoRestante -= $montoPagado;
            } else {
                $nuevoMontoPagado = $montoPagado - $montoRestante;
                $nuevoMontoPendiente = $montoTotal - $nuevoMontoPagado;

                $pago->update($value['id'], [
                    'monto_pagado' => $nuevoMontoPagado,
                    'monto_pendiente' => $nuevoMontoPendiente,
                    'estado' => 'pendiente'
                ]);

                $montoRestante = 0;
            }
        }
    }

    public function apiLoadPdtFile($rutaFile)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => getenv("API_LOAD_PDT_FILE"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('archivo' => new \CURLFILE($rutaFile)),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: multipart/form-data'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    public function apiLoadPdtArchivos($rutaFile)
    {
        $curl = curl_init();

        $postData = json_encode([
            'filepath' => $rutaFile
        ]);

        curl_setopt_array($curl, array(
            CURLOPT_URL => getenv("API_LOAD_PDT_FILES"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    public function apiLoadContrato($rutaFile)
    {
        $curl = curl_init();

        $postData = json_encode(['pdf_path' => $rutaFile]);

        curl_setopt_array($curl, array(
            CURLOPT_URL => getenv("API_LOAD_CONTRATO"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }

    public function getSchemasRestaurantes($ruc)
    {
        $db = \Config\Database::connect('restaurant');

        // 1. Traer los esquemas válidos
        $query = $db->query("SELECT schema_name FROM information_schema.schemata WHERE schema_name NOT IN ('pg_catalog', 'pg_toast', 'information_schema', '_generic', 'esrestaurant', 'public') AND schema_name NOT LIKE 'pg_toast%' AND schema_name NOT LIKE '%_data%'");

        $schemas = $query->getResultArray();

        foreach ($schemas as $schema) {
            $schemaName = $schema['schema_name'];

            try {

                $rpta = $db->query(" SELECT '{$schemaName}' AS schema_name, e.empr_ruc, e.empr_razon_social AS empresa, s.sede_descripcion, s.sede_id AS sede, sc.seco_fecha_vencimiento_suscripcion FROM {$schemaName}.empresa e JOIN {$schemaName}.sede s ON s.empr_id = e.empr_id INNER JOIN {$schemaName}.sede_configuracion sc ON sc.sede_id = s.sede_id WHERE sc.seco_tipo_envio = 'PRODUCCION' and e.empr_ruc = ?", [$ruc])->getResultArray();

                $data = [];

                if ($rpta) {
                    $data['datos'] = $rpta;
                    $data['schemaName'] = $schemaName;

                    return $data;
                }
            } catch (\Throwable $e) {
                // Puede que no exista la tabla empresa, o haya error, así que lo ignoramos
                continue;
            }
        }
    }

    public function updatePagoRestaurante($sedes, $schemaName, $fechaPago, $operacion)
    {
        $db = \Config\Database::connect('restaurant');

        if ($operacion == 1) {
            $nuevaFecha = $this->sumFecha($fechaPago);
        } else {
            $nuevaFecha = $this->restFecha($fechaPago);
        }

        foreach ($sedes as $sede) {
            $sede_id = $sede['sede'];

            $query = $db->query("UPDATE {$schemaName}.sede_configuracion SET seco_fecha_vencimiento_suscripcion = '$nuevaFecha' WHERE sede_id = ?", [$sede_id]);
        }
    }

    function sumFecha($fecha)
    {
        $fecha = new \DateTime($fecha);
        $fecha->modify('+1 month');
        return $fecha->format('Y-m-d');
    }

    function restFecha($fecha)
    {
        $fecha = new \DateTime($fecha);
        $fecha->modify('-1 month');
        return $fecha->format('Y-m-d');
    }

    function sumFechaAnioServidor($fecha)
    {
        $fecha = new \DateTime($fecha);
        $fecha->modify('+1 year');

        $fecha_anio = $fecha->format('Y-m-d');

        $fecha_restar_un_dia = new \DateTime($fecha_anio);
        $fecha_restar_un_dia->modify('-1 day');

        $fecha_anio = $fecha_restar_un_dia->format('Y-m-d');

        return $fecha_anio;
    }

    function sumFechaAnio($fecha)
    {
        $fecha = new \DateTime($fecha);
        $fecha->modify('+1 year');

        $fecha_anio = $fecha->format('Y-m-d');

        return $fecha_anio;
    }

    public function contribuyentesEsFacturador($ruc)
    {
        $db = \Config\Database::connect('facturador');

        $query = $db->query("SELECT * FROM contribuyente WHERE ruc = ?", [$ruc]);

        $contribuyente = $query->getRowArray();

        return $contribuyente;
    }

    public function updateVencimientoFacturador($ruc, $fechaPago, $operacion)
    {
        $db = \Config\Database::connect('facturador');

        if ($operacion == 1) {
            $nuevaFecha = $this->sumFecha($fechaPago);
        } else {
            $nuevaFecha = $this->restFecha($fechaPago);
        }

        $nuevaFecha = date('Y-m-d H:i:s', strtotime($nuevaFecha));

        $query = $db->query("UPDATE contribuyente SET fecha_expiracion = ? WHERE ruc = ?", [$nuevaFecha, $ruc]);

        return true;
    }

    public function getPermisosAcciones($idModulo, $idPerfil, $accion)
    {
        $permiso = new PermisosModel();

        $permisos = $permiso->join('acciones', 'acciones.id = permisos.accion_id')->where('permisos.modulo_id', $idModulo)->where('permisos.perfil_id', $idPerfil)->where('acciones.nombre_accion', $accion)->findAll();

        return $permisos;
    }

    public function getUrlPublicaGoogleCloud($links)
    {
        $data = json_encode(["links" => $links]);

        $url_api = "https://esconsultoresyasesores.com:4000/sendFilesGoogleCloudStorage";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url_api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function linea_trabajo()
    {
        $linea = " OBSERVADOS - 8H Y 40 MIN 	JHONATAN Y LADY 	75076674 / 73750566 	1 (347) 598-8873 / 932 232 991	PREGRADO	Marketing y Negocios Internacionales	UPEU		https://drive.google.com/drive/folders/0B-5tpF2PUOVmflJ6Tlk0NU1NdHBFakVrd1FyLVJnb2NCNmhyaTN1YmxjXzJPZ21nTjBKaXM?resourcekey=0-H-CF_OG1oPSKHyLG2mDjDg&usp=drive_link	05/05	FABRIZZIO 	FLOR 	13/05	";

        $new_linea = explode("\t", $linea);

        echo $new_linea;
    }

    public function certificados_por_vencer()
    {
        $certi = new CertificadoDigitalModel();

        $consulta_certificado_por_vencer = $certi->query("SELECT c.ruc, c.razon_social, cd.tipo_certificado, 
        DATE_FORMAT(cd.fecha_inicio, '%d-%m-%Y') as fecha_inicio, 
        DATE_FORMAT(cd.fecha_vencimiento, '%d-%m-%Y') as fecha_vencimiento
        FROM certificado_digital cd
        INNER JOIN contribuyentes c ON c.id = cd.contribuyente_id
        INNER JOIN sistemas_contribuyente sc ON sc.contribuyente_id = c.id
        WHERE cd.fecha_vencimiento <= DATE_ADD(NOW(), INTERVAL 30 DAY) 
        AND cd.estado = 1 
        AND c.estado = 1
        AND sc.system_id != 3
        ORDER BY cd.fecha_vencimiento ASC;")->getResult();

        return $consulta_certificado_por_vencer;
    }

    public function diferencia_periodos($periodo1, $periodo2)
    {
        $fecha1 = DateTime::createFromFormat('Y-m', $periodo1);
        $fecha2 = DateTime::createFromFormat('Y-m', $periodo2);

        $diff = $fecha1->diff($fecha2);

        $meses = ($diff->y * 12) + $diff->m;

        return $meses;
    }

    public function notificacionSire()
    {
        $sire = new SireModel();
        $contrib = new ContribuyenteModel();
        $fecha_declaracion = new FechaDeclaracionModel();

        $hoy = date('Y-m-d');

        //consulta de las notificaciones
        $declaracion = $fecha_declaracion->query("SELECT fd.id_anio, fd.id_mes, fd.id_numero, fd.fecha_exacta, fd.fecha_notificar, a.anio_descripcion, m.mes_descripcion FROM `fecha_declaracion` AS fd INNER JOIN anio as a ON a.id_anio = fd.id_anio INNER JOIN mes as m ON m.id_mes = fd.id_mes WHERE fd.id_tributo = 27 and fd.id_anio >= 11 and fd.fecha_notificar >= '2025-12-01' and fd.fecha_exacta is not null and fd.fecha_notificar <= '$hoy'")->getResultArray();

        $data_declarar = [];

        foreach ($declaracion as $key => $value) {
            $digito = $value['id_numero'] - 1;

            $listaContrib = $contrib->query("SELECT id, razon_social, ruc, created_at FROM contribuyentes WHERE estado = 1 AND tipoServicio = 'CONTABLE' AND ruc != '10463333748' AND RIGHT(ruc, 1) = $digito")->getResultArray();

            foreach ($listaContrib as $keys => $values) {
                $id = $values['id'];

                $mes_anio = date('Y-m', strtotime($values['created_at']));

                $mes_anio_actual = date('Y-m');

                $diferencia = $this->diferencia_periodos($mes_anio, $mes_anio_actual);

                $excluir = 'NO';

                if ($diferencia == 1) {
                    $excluir = 'SI';
                }

                $querySire = $sire->where('contribuyente_id', $id)->where('periodo', $value['id_mes'])->where('anio', $value['id_anio'])->where('estado', 1)->first();

                if (!$querySire) {
                    $insert = [
                        "contribuyente_id" => $id,
                        "contribuyente" => $values['razon_social'],
                        "anio" => $value['anio_descripcion'],
                        "mes" => $value['mes_descripcion'],
                        "id_mes" => $value['id_mes'],
                        "id_anio" => $value['id_anio'],
                        "excluir" => $excluir
                    ];

                    array_push($data_declarar, $insert);
                }
            }
        }

        return $data_declarar;
    }

    public function notificar_afp()
    {
        $afp = new AfpModel();
        $contrib = new ContribuyenteModel();
        $fecha_declaracion = new FechaDeclaracionModel();

        $hoy = date('Y-m-d');

        $contrib_afp = $contrib->select('contribuyentes.id, contribuyentes.ruc, contribuyentes.razon_social')->join('configuracion_notificacion', 'configuracion_notificacion.ruc_empresa_numero = contribuyentes.ruc')->where('configuracion_notificacion.id_tributo', 22)->where('contribuyentes.tipoServicio', 'CONTABLE')->where('contribuyentes.estado', 1)->findAll();

        //consulta de las notificaciones
        $declaracion = $fecha_declaracion->query("SELECT fd.id_anio, fd.id_mes, fd.fecha_exacta, fd.fecha_notificar, a.anio_descripcion, m.mes_descripcion FROM `fecha_declaracion` AS fd INNER JOIN anio as a ON a.id_anio = fd.id_anio INNER JOIN mes as m ON m.id_mes = fd.id_mes WHERE fd.id_tributo = 22 and fd.id_anio >= 11 and fd.fecha_exacta is not null and fd.fecha_notificar <= '$hoy' GROUP BY fd.id_anio, fd.id_mes, fd.fecha_exacta, fd.fecha_notificar, a.anio_descripcion, m.mes_descripcion;")->getResultArray();

        $data_notificacion = [];

        foreach ($declaracion as $key => $value) {
            $idanio = $value['id_anio'];
            $idmes = $value['id_mes'];

            foreach ($contrib_afp as $keys => $values) {
                $id = $values['id'];

                $verificar_afp = $afp->query("SELECT af.id, af.periodo, af.anio, a.anio_descripcion, m.mes_descripcion FROM afp af INNER JOIN anio as a ON a.id_anio = af.anio INNER JOIN mes as m ON m.id_mes = af.periodo WHERE af.contribuyente_id = $id AND af.anio = $idanio AND af.periodo = $idmes AND af.estado = 1")->getResultArray();

                if (!$verificar_afp) {
                    $insert = [
                        "contribuyente_id" => $id,
                        "contribuyente" => $values['razon_social'],
                        "mes" => $value['mes_descripcion'],
                        "anio" => $value['anio_descripcion']
                    ];

                    array_push($data_notificacion, $insert);
                }
            }
        }

        return $data_notificacion;
    }

    public function notificationPdtRenta()
    {
        $fechaDeclaracion = new FechaDeclaracionModel();
        $cont = new ContribuyenteModel();
        $pdt = new PdtRentaModel();

        $array = [];

        $vencimientos = $fechaDeclaracion->query("SELECT fd.id_anio, fd.id_mes, fd.id_numero, fd.fecha_exacta, DATE_SUB(fd.fecha_exacta, INTERVAL 2 DAY) AS nueva_fecha, m.mes_descripcion, a.anio_descripcion FROM fecha_declaracion fd INNER JOIN mes m ON m.id_mes = fd.id_mes INNER JOIN anio a ON a.id_anio = fd.id_anio where fd.id_tributo = 2 and fd.fecha_exacta BETWEEN '2025-07-01' and CURDATE() + INTERVAL 2 DAY")->getResultArray();

        foreach ($vencimientos as $key => $value) {
            $id_anio = $value['id_anio'];
            $id_mes = $value['id_mes'];
            $id_numero = $value['id_numero'];
            $anio_des = (int) $value['anio_descripcion'];

            $digito = $id_numero - 1;

            $contribuyentes = $cont->select('id, razon_social, ruc, fechaContrato, IF(MONTH(fechaContrato) = MONTH(CURDATE()) AND YEAR(fechaContrato) <= YEAR(CURDATE()), "actual", "antiguo") AS tipo_contrato')->where('estado', 1)->where('RIGHT(ruc, 1)', $digito)->where('tipoServicio', 'CONTABLE')->findAll();

            foreach ($contribuyentes as $keys => $values) {
                $ruc = $values['ruc'];

                $mes = (int)date("m", strtotime($values['fechaContrato']));
                $anio = (int)date("Y", strtotime($values['fechaContrato']));

                if ($id_mes >= $mes && $anio_des >= $anio) {
                    $pdtRenta = $pdt->query("SELECT id_pdt_renta FROM pdt_renta where ruc_empresa = '$ruc' and periodo = $id_mes and anio = $id_anio and estado = 1")->getResultArray();

                    if (!$pdtRenta) {
                        $renta = $pdt->query("SELECT id_pdt_renta FROM pdt_renta where ruc_empresa = '$ruc'")->getResultArray();

                        $registro = 0;

                        if ($renta) {
                            $registro = 1;
                        }

                        $array[] = [
                            'contribuyente_id' => $values['id'],
                            'ruc' => $ruc,
                            'razon_social' => $values['razon_social'],
                            'anio' => $value['anio_descripcion'],
                            'mes' => $value['mes_descripcion'],
                            'numero' => $id_numero - 1,
                            'fecha_exacta' => $value['fecha_exacta'],
                            'fechaContrato' => $values['fechaContrato'],
                            'tipo_contrato' => $values['tipo_contrato'],
                            'id_anio' => $id_anio,
                            'id_mes' => $id_mes,
                            'registro' => $registro
                        ];
                    }
                }
            }
        }

        return $array;
    }

    public function notificationPdtPlame()
    {
        $fechaDeclaracion = new FechaDeclaracionModel();
        $cont = new ContribuyenteModel();
        $pdt = new PdtPlameModel();

        $array = [];

        $vencimientos = $fechaDeclaracion->query("SELECT fd.id_anio, fd.id_mes, fd.id_numero, fd.fecha_exacta, DATE_SUB(fd.fecha_exacta, INTERVAL 2 DAY) AS nueva_fecha, m.mes_descripcion, a.anio_descripcion FROM fecha_declaracion fd INNER JOIN mes m ON m.id_mes = fd.id_mes INNER JOIN anio a ON a.id_anio = fd.id_anio where fd.id_tributo = 2 and fd.fecha_exacta BETWEEN '2025-07-01' and CURDATE() + INTERVAL 2 DAY")->getResultArray();

        foreach ($vencimientos as $key => $value) {
            $id_anio = $value['id_anio'];
            $id_mes = $value['id_mes'];
            $id_numero = $value['id_numero'];

            $digito = $id_numero - 1;

            $contribuyentes = $cont->query("SELECT c.ruc, MAX(c.id) AS id, MAX(c.razon_social) AS razon_social, MAX(c.fechaContrato) AS fechaContrato, IF(MONTH(MAX(c.fechaContrato)) <= MONTH(CURDATE()) AND YEAR(MAX(c.fechaContrato)) = YEAR(CURDATE()), 'actual', 'antiguo') AS tipo_contrato FROM contribuyentes c INNER JOIN configuracion_notificacion cn ON cn.ruc_empresa_numero = c.ruc INNER JOIN tributo t ON t.id_tributo = cn.id_tributo WHERE c.estado = 1 AND RIGHT(c.ruc, 1) = $digito AND c.tipoServicio = 'CONTABLE' AND t.id_pdt = 2 GROUP BY c.ruc")->getResultArray();

            foreach ($contribuyentes as $keys => $values) {
                $ruc = $values['ruc'];

                $pdtPlame = $pdt->query("SELECT pp.id_pdt_plame, ap.archivo_constancia, pp.excluido FROM pdt_plame pp LEFT JOIN archivos_pdtplame ap ON ap.id_pdtplame = pp.id_pdt_plame where pp.ruc_empresa = '$ruc' and pp.periodo = $id_mes and pp.anio = $id_anio and pp.estado = 1 ORDER BY ap.id_archivos_pdtplame desc")->getRowArray();

                if ($pdtPlame) {
                    if (($pdtPlame['archivo_constancia'] === null || $pdtPlame['archivo_constancia'] === '') && $pdtPlame['excluido'] === 'NO') {
                        $array[] = [
                            'contribuyente_id' => $values['id'],
                            'ruc' => $ruc,
                            'razon_social' => $values['razon_social'],
                            'anio' => $value['anio_descripcion'],
                            'mes' => $value['mes_descripcion'],
                            'numero' => $id_numero - 1,
                            'fecha_exacta' => $value['fecha_exacta'],
                            'fechaContrato' => $values['fechaContrato'],
                            'tipo_contrato' => $values['tipo_contrato'],
                            'id_anio' => $id_anio,
                            'id_mes' => $id_mes
                        ];
                    }
                } else {
                    $array[] = [
                        'contribuyente_id' => $values['id'],
                        'ruc' => $ruc,
                        'razon_social' => $values['razon_social'],
                        'anio' => $value['anio_descripcion'],
                        'mes' => $value['mes_descripcion'],
                        'numero' => $id_numero - 1,
                        'fecha_exacta' => $value['fecha_exacta'],
                        'fechaContrato' => $values['fechaContrato'],
                        'tipo_contrato' => $values['tipo_contrato'],
                        'id_anio' => $id_anio,
                        'id_mes' => $id_mes
                    ];
                }
            }
        }

        return $array;
    }

    public function renderContribuyentesDeuda($servicio, $estado)
    {
        $contribuyente = new ContribuyenteModel();
        $sistema = new SistemaModel();
        $pagoServidor = new PagoServidorModel();
        $servidor = new ServidorModel();

        $cobrar = $this->getPermisosAcciones(13, session()->perfil_id, 'cobrar servidor');

        $sqlServicio = "";

        if ($servicio != 'TODOS') {
            $sqlServicio = "AND c.tipoServicio = '" . $servicio . "'";
        }

        $contribuyentes = $contribuyente->query("SELECT 
            c.id,
            c.ruc,
            c.razon_social
        FROM contribuyentes c
        INNER JOIN sistemas_contribuyente sc ON c.id = sc.contribuyente_id
        INNER JOIN sistemas s ON sc.system_id = s.id
        LEFT JOIN pago_servidor ps ON (
            c.id = ps.contribuyente_id 
            AND ps.estado = 'pendiente' 
            AND ps.fecha_inicio < CURDATE()
        )
        WHERE s.status = 1
            AND c.tipoServicio = 'CONTABLE'
            AND c.tipoSuscripcion = 'NO GRATUITO'
            AND c.estado = $estado
            $sqlServicio
        GROUP BY c.id, c.ruc, c.razon_social;")->getResultArray();

        foreach ($contribuyentes as $key => $value) {
            $sistemas = $sistema->query("SELECT s.id, s.nameSystem FROM sistemas s INNER JOIN sistemas_contribuyente sc ON s.id = sc.system_id WHERE sc.contribuyente_id = " . $value['id'])->getResultArray();
            $contribuyentes[$key]['sistemas'] = $sistemas;

            $monto = $servidor->where('contribuyente_id', $value['id'])->where('estado', 1)->first();

            if ($monto) {
                $contribuyentes[$key]['monto'] = $monto['monto'];
            } else {
                $contribuyentes[$key]['monto'] = "";
            }

            $verificarRegistros = $pagoServidor
                ->select("DATE_FORMAT(fecha_inicio, '%d-%m-%Y') as fecha_inicio, DATE_FORMAT(fecha_fin, '%d-%m-%Y') as fecha_fin, fecha_inicio as fecha_inicio_raw, fecha_fin as fecha_fin_raw, estado")
                ->where('contribuyente_id', $value['id'])
                ->where('estado !=', 'eliminado')
                ->orderBy('id', 'desc')
                ->first();

            if (!$verificarRegistros) {
                $contribuyentes[$key]['pagos'] = "NO TIENE REGISTROS";
                $contribuyentes[$key]['fecha_inicio'] = "";
                $contribuyentes[$key]['fecha_fin'] = "";
                $contribuyentes[$key]['fecha_inicio_raw'] = "";
                $contribuyentes[$key]['fecha_fin_raw'] = "";

                $contribuyentes[$key]['periodos'] = "0";
            } else {
                $contribuyentes[$key]['fecha_inicio'] = $verificarRegistros['fecha_inicio'];
                $contribuyentes[$key]['fecha_fin'] = $verificarRegistros['fecha_fin'];

                $fechaActual = new \DateTime(); // hoy
                $fechaInicio = new \DateTime($verificarRegistros['fecha_inicio_raw']);
                $fechaFin    = new \DateTime($verificarRegistros['fecha_fin_raw']);

                $periodos = 0;

                if ($fechaActual >= $fechaInicio && $fechaActual <= $fechaFin) {
                    if ($verificarRegistros['estado'] == 'pendiente') {
                        $periodos += 1;
                    }
                }

                if ($fechaActual > $fechaFin) {
                    $periodosVencidos = 0;
                    $fechaTemp = clone $fechaFin;

                    // mientras la fecha actual supere el fin del periodo
                    while ($fechaActual > $fechaTemp) {
                        $periodosVencidos++;
                        $fechaTemp->modify('+1 year');
                    }

                    $periodos += $periodosVencidos;
                }

                if ($periodos == 0) {
                    $contribuyentes[$key]['pagos'] = "NO DEBE";
                } else if ($periodos == 1) {
                    $contribuyentes[$key]['pagos'] = "1 PERIODO";
                } else {
                    $contribuyentes[$key]['pagos'] = $periodos . " PERIODOS";
                }

                $contribuyentes[$key]['fecha_inicio_raw'] = $verificarRegistros['fecha_inicio_raw'];
                $contribuyentes[$key]['fecha_fin_raw'] = $verificarRegistros['fecha_fin_raw'];

                $contribuyentes[$key]['periodos'] = $periodos;
            }

            $cobrarSer = "";

            if ($cobrar) {
                $cobrarSer = "<a href='" . base_url() . "cobrar-servidor/" . $value['id'] . "' class='btn btn-success'>COBRAR</a>";
            }

            $contribuyentes[$key]['cobrar'] = $cobrarSer;
        }

        return $contribuyentes;
    }

    public function countAllServidorDeuda()
    {
        $todos = $this->renderContribuyentesDeuda('TODOS', 1);

        $deudores = [];

        foreach ($todos as $key => $value) {
            if ($value['periodos'] > 0) {
                $deudores[] = $value;
            }
        }

        $count = count($deudores);
        $clientes = $deudores;

        return ['count' => $count, 'clientes' => $clientes];
    }

    public function renderDeudoresAnuales()
    {
        $contribuyente = new ContribuyenteModel();
        $fecha = new FechaDeclaracionModel();
        $anio = new AnioModel();
        $pagoAnual = new PagoAnualModel();
        $pdtAnual = new PdtAnualModel();

        $fecha->query("SET lc_time_names = 'es_ES'");

        $actual = '2025';

        $anioActual = $anio->where('anio_descripcion', $actual)->first();
        $idanio = $anioActual['id_anio'];

        $data = $fecha->query("SELECT fd.fecha_exacta, MAX(fd.id_fecha_declaracion) as id_fecha_declaraccion,MAX(fd.id_anio) as id_anio, MAX(fd.id_numero) as id_numero, MAX(fd.id_tributo) as id_tributo, MAX(fd.dia_exacto) as dia_exacto, MAX(fd.fecha_notificar) as fecha_notificar, MAX(a.anio_descripcion) as anio_descripcion, MAX(t.id_pdt) as id_pdt, MAX(t.tri_descripcion) as tri_descripcion FROM `fecha_declaracion` fd INNER JOIN tributo t ON t.id_tributo = fd.id_tributo INNER JOIN anio a ON a.id_anio = fd.id_anio WHERE t.id_pdt = 3 and fd.id_anio >= $idanio and fd.dia_exacto != 0 GROUP BY fd.fecha_exacta order by MAX(fd.id_fecha_declaracion) asc;")->getResultArray();

        $empresas = [];

        foreach ($data as $key => $value) {
            $fechaExacta = $value['fecha_exacta'];
            $fechaNotificar = $value['fecha_notificar'];

            if (date('Y-m-d') >= $fechaNotificar) {
                $digito = $value['id_numero'] - 1;
                $datos = $contribuyente->query("SELECT c.id, c.ruc, c.razon_social FROM contribuyentes c INNER JOIN configuracion_notificacion cn ON cn.ruc_empresa_numero = c.ruc where cn.id_tributo IN (11, 12, 13, 14) and c.estado = 1 and c.tipoServicio = 'CONTABLE' and RIGHT(c.ruc, 1) = $digito GROUP BY c.id, c.ruc, c.razon_social;")->getResultArray();

                foreach ($datos as $keys => $values) {
                    $idc = $values['id'];
                    $ruc = $values['ruc'];
                    $razonSocial = $values['razon_social'];

                    $existePdtAnual = $pdtAnual->where('ruc_empresa', $ruc)->where('id_pdt_tipo', 3)->where('periodo', $value['id_anio'])->where('estado', 1)->first();

                    if ($existePdtAnual) {

                        if ($existePdtAnual['cargo'] == 1) {
                            $existePagoAnual = $pagoAnual->where('contribuyente_id', $idc)->where('anio_correspondiente', $value['anio_descripcion'])->where('estado', 'pendiente')->first();

                            if ($existePagoAnual) {
                                $mensaje = "Falta Pago Anual";

                                $data_emp = [
                                    "id" => $idc,
                                    "ruc" => $ruc,
                                    "razon_social" => $razonSocial,
                                    'mensaje' => $mensaje,
                                    'anio' => $value['anio_descripcion'],
                                ];

                                array_push($empresas, $data_emp);
                            }
                        }
                    } else {
                        $mensaje = "Falta subir su pdt anual";

                        $data_emp = [
                            "id" => $idc,
                            "ruc" => $ruc,
                            "razon_social" => $razonSocial,
                            'mensaje' => $mensaje,
                            'anio' => $value['anio_descripcion'],
                        ];

                        array_push($empresas, $data_emp);
                    }
                }
            }
        }

        return $empresas;
    }

    public function api_read_boleta_pago($path)
    {
        $curl = curl_init();

        $postData = json_encode([
            'pdf_path' => $path
        ]);

        curl_setopt_array($curl, array(
            CURLOPT_URL => getenv("API_READ_BOLETA_PAGO"),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    
}
