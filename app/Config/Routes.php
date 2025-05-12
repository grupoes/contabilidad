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
$routes->get('auth/asignar/(:num)', 'Auth::asignar/$1');
$routes->post('save-asignar', 'Auth::saveAsignar');
$routes->get('get-user/(:num)', 'Auth::getUser/$1');
$routes->get('user/delete/(:num)', 'Auth::deleteUser/$1');

$routes->get('migration-users', 'Auth::migrationUsers');

$routes->get('home', 'Home::index');

$routes->get('contribuyentes', 'Contribuyentes::index');
$routes->get('contribuyentes/getId/(:num)', 'Contribuyentes::getIdContribuyente/$1');
$routes->get('contribuyentes/render', 'Contribuyentes::renderContribuyentes');
$routes->get('contribuyentes/renderContribuyentesContables', 'Contribuyentes::renderContribuyentesContables');
$routes->post('contribuyente/add', 'Contribuyentes::guardar');
$routes->post('contribuyente/add-tarifa', 'Contribuyentes::guardarTarifa');
$routes->get('contribuyente/all/(:any)/(:num)', 'Contribuyentes::listaContribuyentes/$1/$2');
$routes->get('contribuyente/status/(:num)/(:num)', 'Contribuyentes::changeStatus/$1/$2');
$routes->get('contribuyente/get/(:num)', 'Contribuyentes::getContribuyente/$1');
$routes->get('contribuyente/historial-tarifa/(:num)', 'Contribuyentes::getTarifaContribuyente/$1');
$routes->get('contribuyente/delete-tarifa/(:num)', 'Contribuyentes::deleteTarifa/$1');
$routes->get('contribuyente/certificado-digital/(:num)', 'Contribuyentes::getCertificadoDigital/$1');
$routes->post('contribuyente/add-certificado', 'Contribuyentes::guardarCertificadoDigital');
$routes->get('descargar-certificado/(:any)', 'Contribuyentes::descargarCertificado/$1');
$routes->get('contribuyente/delete-certificado-digital/(:num)', 'Contribuyentes::deleteCertificadoDigital/$1');
$routes->get('contribuyentes/paises', 'Contribuyentes::prefijosPaises');
$routes->post('contribuyente/add-contacto', 'Contribuyentes::addContacto');
$routes->get('contribuyente/contactos/(:num)', 'Contribuyentes::renderContactos/$1');
$routes->get('contribuyente/get-contacto/(:num)', 'Contribuyentes::getContacto/$1');
$routes->get('contribuyente/delete-contacto/(:num)', 'Contribuyentes::deleteContacto/$1');
$routes->get('contribuyente/delete/(:num)', 'Contribuyentes::deleteContribuyente/$1');
$routes->get('contribuyente/declaracion/(:num)', 'Contribuyentes::declaracion/$1');
$routes->post('contribuyente/configurar-declaracion', 'Contribuyentes::configurarDeclaracion');
$routes->post('contribuyente/importar-boletas', 'Contribuyentes::importarBoletas');
$routes->post('descargar/excelComprobantes', 'Contribuyentes::descargarExcelComprobantes');
$routes->post('contribuyente/vacear-boletas', 'Contribuyentes::vacearBoletas');
$routes->get('contribuyente/ver-acceso/(:num)', 'Contribuyentes::verAcceso/$1');
$routes->post('contribuyente/actualizar-clave', 'Contribuyentes::updatePassword');
$routes->get('contribuyentes/contribuyentesActivos/(:any)', 'Contribuyentes::getContribuyenteActivos/$1');

$routes->get('contribuyentes/migracion', 'Contribuyentes::migrarContribuyentes');

$routes->get('cobros', 'Contribuyentes::allCobros');
$routes->get('listaCobros/(:any)/(:num)', 'Contribuyentes::listaHonorariosCobros/$1/$2');

$routes->get('pago-honorario/(:num)', 'Pago::pagosHonorarios/$1');
$routes->get('pagos/lista-pagos/(:num)', 'Pago::listaPagos/$1');
$routes->get('pagos/lista-pagos-honorarios/(:num)', 'Pago::listaPagosHonorarios/$1');
$routes->post('pagos/pagar-honorario', 'Pago::pagarHonorario');
$routes->get('pagos', 'Pago::index');
$routes->get('pagos/renderPagos', 'Pago::renderPagos');
$routes->get('pagos/actualizar', 'Pago::insertContratos');

$routes->get('all-ubigeo', 'Contribuyentes::listaUbigeo');

$routes->get('caja-diaria', 'Caja::index');
$routes->get('caja/apertura', 'Caja::Aperturar');
$routes->get('caja/cierreCaja', 'Caja::cierreCaja');
$routes->get('caja/validar-caja', 'Caja::validarcaja');

$routes->get('caja/resumen-cajero', 'Caja::resumenCajaDiaria');
$routes->get('resumenCajaDia', 'Caja::resumenCajaDia');
$routes->get('resumenCajaDiaAll', 'Caja::resumenCajaDiaAll');

$routes->get('movimientos', 'Movimiento::index');
$routes->post('movimiento/guardar', 'Movimiento::guardar');
$routes->get('movimientos/lista-cajero/(:any)', 'Movimiento::showCajero/$1');
$routes->get('movimientos/metodos-pagos', 'Movimiento::allMetodoPagos');
$routes->get('movimiento/extornar/(:num)', 'Movimiento::extornar/$1');
$routes->post('movimiento/cambio-pago', 'Movimiento::cambioPago');

$routes->get('movimiento-bancos', 'Movimiento::bancosMovimientos');

$routes->get('conceptos', 'Concepto::index');
$routes->post('concepto/guardar', 'Concepto::save');
$routes->get('render-conceptos', 'Concepto::renderConceptos');
$routes->get('conceptos-tipo-movimiento/(:num)', 'Concepto::conceptosTipoMovimiento/$1');
$routes->get('concepto/delete/(:num)', 'Concepto::deleteConcepto/$1');

$routes->get('configuracion/caja-virtual', 'Configuracion::cajaVirtual');
$routes->post('configuracion-caja-virtual/save', 'Configuracion::saveCajaVirtual');

$routes->post('send-file-google-cloud-storage', 'Configuracion::sendFileGoogleCloudStorage');

$routes->get('configuracion/uit', 'Configuracion::Uit');
$routes->post('configuracion/save-uit', 'Configuracion::saveUit');
$routes->get('configuracion/renta', 'Configuracion::renta');
$routes->get('configuracion/contadores', 'Configuracion::contadores');
$routes->get('configuracion/render-contadores', 'Configuracion::renderContadores');
$routes->get('configuracion/elegir-contador/(:num)', 'Configuracion::elegirContador/$1');
$routes->get('configuracion/get-contador/(:num)', 'Configuracion::getContador/$1');
$routes->post('configuracion/save-contador', 'Configuracion::saveContador');
$routes->get('configuracion/delete-contador/(:num)', 'Configuracion::deleteContador/$1');
$routes->get('configuracion/rentasAnuales/(:num)', 'Configuracion::getRentas/$1');
$routes->post('configuracion/rentasAnuales/actualizar', 'Configuracion::updateRentas');

$routes->get('declaraciones/pdt-0621', 'Pdt0621::index');
$routes->post('consulta-pdt-renta', 'Pdt0621::consulta');
$routes->post('contribuyentes/file-save-pdt0621', 'Pdt0621::filesSave');
$routes->post('consulta-pdt-rango', 'Pdt0621::consultaPdt');
$routes->post('send-file-pdt621', 'Pdt0621::sendMessageFiles');

$routes->get('declaraciones/pdt-plame', 'PdtPlame::index');
$routes->post('contribuyentes/file-save-pdtplame', 'PdtPlame::filesSave');
$routes->post('consulta-pdt-plame', 'PdtPlame::consulta');

$routes->get('declaraciones/pdt-anual', 'PdtAnual::index');
$routes->get('pdtAnual/verificar/(:num)', 'PdtAnual::verificar/$1');
$routes->post('pdtAnual-consulta', 'PdtAnual::consulta');

$routes->get('declaraciones/boleta-de-pago', 'BoletaPago::index');
$routes->post('boleta-pago-save', 'BoletaPago::save');
$routes->post('boletas-pago-load', 'BoletaPago::consulta');

$routes->get('bancos', 'Bancos::index');
$routes->get('bancos/all', 'Bancos::show');
$routes->post('banco/guardar', 'Bancos::save');
$routes->get('banco/get-banco/(:num)', 'Bancos::getBanco/$1');
$routes->get('banco/delete/(:num)', 'Bancos::delete/$1');

$routes->get('metodos-de-pago', 'MetodoPago::index');
$routes->get('metodos/all', 'MetodoPago::show');
$routes->post('metodo-pago/guardar', 'MetodoPago::save');
$routes->get('metodo-pago/get-metodo/(:num)', 'MetodoPago::getMetodo/$1');
$routes->get('metodo-pago/delete/(:num)', 'MetodoPago::delete/$1');

$routes->get('asignar-contribuyentes', 'Auth::asignarContribuyentes');

$routes->get('permisos', 'Permisos::index');
$routes->get('permisos-perfil/(:num)', 'Permisos::show/$1');
$routes->post('save-permisos', 'Permisos::guardar');
$routes->get('menu-nav', 'Permisos::permisos_menu');

$routes->get('declaracion', 'Declaracion::index');
$routes->get('listaDeclaracion/(:num)', 'Declaracion::listaDeclaracion/$1');
$routes->post('declaracion/calendario', 'Declaracion::calendario');
$routes->post('declaracion/extraer_data', 'Declaracion::extraer_data');
$routes->post('declaracion/guardar_datos', 'Declaracion::guardar_datos');

$routes->get('empresas-notificacion', 'Api\Notificaciones::index');
$routes->get('mensajes-pendientes', 'Api\Notificaciones::mensajesPendientes');
$routes->post('update-mensaje', 'Api\Notificaciones::updateMessage');
$routes->get('send-email', 'Api\Notificaciones::sendEmail');

$routes->get('mensajes-masivos', 'Mensajes::index');
$routes->post('mensajes/guardarMensajeMasivo', 'Mensajes::guardarMensajeMasivo');
$routes->get('lista-mensajes', 'Mensajes::listaMensajes');
$routes->get('all-mensaje', 'Mensajes::mensajesAll');
$routes->get('mensajes-all-id/(:num)', 'Mensajes::mensajesAllId/$1');

$routes->get('numeros-whatsapp', 'NumeroWhatsapp::index');
$routes->get('configuracion/numeroWhatsapp/all', 'NumeroWhatsapp::allNumeroWhatsapp');
$routes->post('configuracion/numeroWhatsapp/store', 'NumeroWhatsapp::saveNumeroWhatsapp');
$routes->get('configuracion/numeroWhatsapp/(:num)', 'NumeroWhatsapp::getIdNumeroWhatsapp/$1');
$routes->get('configuracion/deleteWhatsapp/(:num)', 'NumeroWhatsapp::deleteWhatsapp/$1');
