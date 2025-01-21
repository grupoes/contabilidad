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

        if($tipoPago == 1) {
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
            $fechaPago = $fechaContratoObj->format('Y-m') . "-".$dataContrib['diaCobro'];

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
}
