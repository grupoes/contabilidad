<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//$routes->get('/', 'Home::index');

$routes->get('/', 'Auth::index');
$routes->post('auth/login', 'Auth::login');
$routes->get('auth/logout', 'Auth::logout');
$routes->get('usuarios', 'Auth::userAll');
$routes->get('api/dni-ruc/(:any)/(:any)', 'Auth::api_dni_ruc/$1/$2');
$routes->post('save-user', 'Auth::guardarUsuario');
$routes->get('all-users', 'Auth::showUsers');

$routes->get('home', 'Home::index');

$routes->get('contribuyentes', 'Contribuyentes::index');
$routes->get('contribuyentes/render', 'Contribuyentes::renderContribuyentes');
$routes->post('contribuyente/add', 'Contribuyentes::guardar');
$routes->post('contribuyente/add-tarifa', 'Contribuyentes::guardarTarifa');
$routes->get('contribuyente/all/(:any)', 'Contribuyentes::listaContribuyentes/$1');
$routes->get('contribuyente/status/(:num)/(:num)', 'Contribuyentes::changeStatus/$1/$2');
$routes->get('contribuyente/get/(:num)', 'Contribuyentes::getContribuyente/$1');
$routes->get('contribuyente/historial-tarifa/(:num)', 'Contribuyentes::getTarifaContribuyente/$1');
$routes->get('contribuyente/delete-tarifa/(:num)', 'Contribuyentes::deleteTarifa/$1');
$routes->get('contribuyente/certificado-digital/(:num)', 'Contribuyentes::getCertificadoDigital/$1');
$routes->post('contribuyente/add-certificado', 'Contribuyentes::guardarCertificadoDigital');
$routes->get('descargar-certificado/(:any)', 'Contribuyentes::descargarCertificado/$1');
$routes->get('contribuyente/delete-certificado-digital/(:num)', 'Contribuyentes::deleteCertificadoDigital/$1');

$routes->get('cobros', 'Contribuyentes::allCobros');
$routes->get('listaCobros/(:any)', 'Contribuyentes::listaHonorariosCobros/$1');

$routes->get('pago-honorario/(:num)', 'Pago::pagosHonorarios/$1');
$routes->get('pagos/lista-pagos/(:num)', 'Pago::listaPagos/$1');
$routes->get('pagos/lista-pagos-honorarios/(:num)', 'Pago::listaPagosHonorarios/$1');
$routes->post('pagos/pagar-honorario', 'Pago::pagarHonorario');
$routes->get('pagos', 'Pago::index');
$routes->get('pagos/renderPagos', 'Pago::renderPagos');

$routes->get('all-ubigeo', 'Contribuyentes::listaUbigeo');

$routes->get('caja-diaria', 'Caja::index');
$routes->get('caja/apertura', 'Caja::Aperturar');
$routes->get('caja/cierreCaja', 'Caja::cierreCaja');

$routes->get('movimientos', 'Movimiento::index');
$routes->post('movimiento/guardar', 'Movimiento::guardar');
$routes->get('movimientos/lista-cajero/(:any)', 'Movimiento::showCajero/$1');
$routes->get('movimientos/metodos-pagos', 'Movimiento::allMetodoPagos');

$routes->get('conceptos', 'Concepto::index');
$routes->get('render-conceptos', 'Concepto::renderConceptos');
$routes->get('conceptos-tipo-movimiento/(:num)', 'Concepto::conceptosTipoMovimiento/$1');

$routes->get('configuracion/caja-virtual', 'Configuracion::cajaVirtual');
$routes->post('configuracion-caja-virtual/save', 'Configuracion::saveCajaVirtual');

$routes->get('configuracion/uit', 'Configuracion::Uit');
$routes->post('configuracion/save-uit', 'Configuracion::saveUit');
$routes->get('configuracion/renta', 'Configuracion::renta');
$routes->get('configuracion/contadores', 'Configuracion::contadores');
$routes->get('configuracion/render-contadores', 'Configuracion::renderContadores');
$routes->get('configuracion/elegir-contador/(:num)', 'Configuracion::elegirContador/$1');

$routes->get('declaraciones/pdt-0621', 'Pdt0621::index');
$routes->get('declaraciones/pdt-plame', 'PdtPlame::index');
$routes->get('declaraciones/pdt-anual', 'PdtAnual::index');
$routes->get('declaraciones/boleta-de-pago', 'BoletaPago::index');