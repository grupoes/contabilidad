<?php

namespace App\Controllers;

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
            ->groupBy('modulos.modulo_padre')
            ->findAll();

        foreach ($modulos as $key => $value) {
            $hijos = $permisos->select('modulos.id,modulos.nombre,modulos.url, modulos.orden')
                ->join('modulos', 'modulos.id = permisos.modulo_id')
                ->where('permisos.perfil_id', session()->perfil_id)
                ->where('modulos.modulo_padre', $value['modulo_padre'])
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
}
