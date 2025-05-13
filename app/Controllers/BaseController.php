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
                ->findAll();

            $modulos[$key]['hijos'] = $hijos;
        }

        return $modulos;
    }

    public function generarMovimiento($sesionCaja, $concepto, $formaPago, $metodoPago, $monto, $descripcion, $tipoComprobante, $descripcionComprobante, $estado)
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
            'mov_hora' => date('H:i:s'),
            'mov_estado' => $estado,
            'mov_cobro' => 0,
            'userRegister' => session()->id,
            'nombreUser' => session()->nombre . ' ' . session()->apellidos,
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

    public function Aperturar()
    {
        $sesion = new SesionCajaModel();
        $sedeCaja = new SedeCajaModel();

        $sesion->db->transStart();

        try {

            $datos = $this->obtenerCajaSedeVirtual();

            $idUser = $datos['idUser'];
            $sede = $datos['idSede'];

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
                        return [
                            "idSesionFisica" => $sesions[1]['id_sesion_caja'],
                            "idSesionVirtual" => $sesions[0]['id_sesion_caja'],
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
                "status" => "success",
            ];
        } catch (\Exception $e) {
            $sesion->db->transRollback(); // Revertir la transacción
            return [
                "status" => "error",
            ];
        }
    }
}
