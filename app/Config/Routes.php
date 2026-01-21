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
$routes->get('certificados-vencer', 'Home::certificadosVencer');

$routes->get('contribuyentes', 'Contribuyentes::index');
$routes->get('contribuyentes/getId/(:num)', 'Contribuyentes::getIdContribuyente/$1');
$routes->get('contribuyentes/render', 'Contribuyentes::renderContribuyentes');
$routes->get('contribuyentes/contables/(:num)', 'Contribuyentes::contribuyentesContables/$1');
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
$routes->get('excel/download/(:any)', 'Contribuyentes::download/$1');
$routes->post('contribuyente/vacear-boletas', 'Contribuyentes::vacearBoletas');
$routes->get('contribuyente/ver-acceso/(:num)', 'Contribuyentes::verAcceso/$1');
$routes->post('contribuyente/actualizar-clave', 'Contribuyentes::updatePassword');
$routes->get('contribuyentes/contribuyentesActivos/(:any)', 'Contribuyentes::getContribuyenteActivos/$1');
$routes->get('contribuyente/contratos/(:num)', 'Contribuyentes::showContratos/$1');
$routes->post('contribuyente/agregar-contrato', 'Contribuyentes::agregarContrato');

$routes->get('contribuyentes/migracion', 'Contribuyentes::migrarContribuyentes');

$routes->get('cobros', 'Contribuyentes::allCobros');
$routes->get('listaCobros/(:any)/(:num)', 'Contribuyentes::listaHonorariosCobros/$1/$2');

$routes->get('servicio', 'Cobros::cobroPlanificador');
$routes->get('crear/servicio', 'Cobros::createCobroServicio');
$routes->post('save-service', 'Cobros::saveService');
$routes->get('services/all', 'Cobros::allServices');

$routes->get('cobro-servidor', 'Cobros::index');
$routes->get('render-contribuyentes/(:any)/(:num)', 'Cobros::renderContribuyentes/$1/$2');
$routes->get('cobrar-servidor/(:num)', 'Cobros::cobrarView/$1');
$routes->get('render-montos/(:num)', 'Cobros::renderMontos/$1');
$routes->post('montos/add-monto', 'Cobros::addMonto');
$routes->get('render-pagos-servidor/(:num)', 'Cobros::renderPagosServidor/$1');
$routes->get('deudores-servidor', 'Cobros::renderContribuyentesDeudaAll');
$routes->get('deudas-anuales/(:any)/(:num)', 'Cobros::getCobrosAnuales/$1/$2');
$routes->get('cobrar-anual/(:num)', 'Cobros::cobrarAnualView/$1');
$routes->get('render-pagos-anuales/(:num)', 'Cobros::renderPagosAnual/$1');
$routes->get('monto-anual/(:num)', 'Cobros::montoAnual/$1');
$routes->post('cobros/pagar-anual', 'Cobros::pagarAnual');
$routes->get('cobros/render-amortizacion/(:num)', 'Cobros::renderAmortizacionAnual/$1');
$routes->get('detail-amort-anual/(:num)', 'Cobros::getPagoAnual/$1');
$routes->get('cobros/delete-pago-anual/(:num)', 'Cobros::deletePagoAnual/$1');
$routes->post('cobros/update-pago-anual', 'Cobros::updatePagoAnual');
$routes->post('cobros/update-voucher-anual', 'Cobros::updateVaucherAnual');
$routes->get('deudores-anuales', 'Cobros::renderDeudoresAnualesAll');

$routes->get('pago-honorario/(:num)', 'Pago::pagosHonorarios/$1');
$routes->get('pagos/lista-pagos/(:num)', 'Pago::listaPagos/$1');
$routes->get('pagos/lista-pagos-honorarios/(:num)', 'Pago::listaPagosHonorarios/$1');
$routes->post('pagos/pagar-honorario', 'Pago::pagarHonorario');
$routes->get('pagos', 'Pago::index');
$routes->get('pagos/renderPagos', 'Pago::renderPagos');
$routes->get('pagos/actualizar', 'Pago::insertContratos');
$routes->get('pagos/delete-pago/(:num)', 'Pago::deletePago/$1');
$routes->post('pagos/update-voucher', 'Pago::updateVaucher');
$routes->get('pagos/get-pago/(:num)', 'Pago::getPago/$1');
$routes->post('pagos/update-pago', 'Pago::updatePago');
$routes->get('pagos/get-monto-pendiente/(:num)', 'Pago::getMontoPendiente/$1');
$routes->get('pagos/historial-pagos/(:num)', 'Pago::historialPagos/$1');

$routes->get('pagos/monto-servidor/(:num)', 'Pago::montoServidor/$1');
$routes->post('pagos/pagar-servidor', 'Pago::pagarServidor');
$routes->get('pagos/render-amortizacion-servidor/(:num)', 'Pago::renderAmortizacionServidor/$1');
$routes->get('pagos/get-pago-servidor/(:num)', 'Pago::getPagoServidor/$1');
$routes->get('pagos/delete-pago-servidor/(:num)', 'Pago::deletePagoServidor/$1');
$routes->post('pagos/update-pago-servidor', 'Pago::updatePagoServidor');
$routes->post('pagos/update-voucher-servidor', 'Pago::updateVaucherServidor');

$routes->get('all-ubigeo', 'Contribuyentes::listaUbigeo');

$routes->get('caja-diaria', 'Caja::index');
$routes->get('caja/apertura/(:num)', 'Caja::Aperturar/$1');
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
$routes->post('movimiento/editar-movimiento', 'Movimiento::editMovimiento');
$routes->post('movimientos/consulta', 'Movimiento::Consulta');

$routes->get('movimientos-generales', 'Movimiento::movimientosGenerales');
$routes->post('movimientos/getMovimientosGenerales', 'Movimiento::getMovimientosGeneralesFilter');

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
$routes->post('rectificacion-pdt-renta', 'Pdt0621::pdtRectificacion');
$routes->get('pdt-0621/get-files-details/(:num)', 'Pdt0621::getArchivos/$1');
$routes->post('pdt-0621/save-montos', 'Pdt0621::updateMontos');
$routes->get('pdt-0621/delete/(:num)/(:num)', 'Pdt0621::delete/$1/$2');
$routes->get('notificacion-pdt-renta', 'Pdt0621::notificacionPdtRenta');

$routes->get('declaraciones/pdt-renta-transacciones', 'Pdt0621::transacciones');
$routes->post('declaraciones/obtenerDatosPdtRentaTransacciones', 'Pdt0621::listEmpresas');
$routes->get('declaraciones/periodosPdtRenta/(:num)/(:num)', 'Pdt0621::listaPeriodos/$1/$2');

$routes->get('declaraciones/pdt-plame', 'PdtPlame::index');
$routes->post('contribuyentes/file-save-pdtplame', 'PdtPlame::filesSave');
$routes->post('consulta-pdt-plame', 'PdtPlame::consulta');
$routes->get('consulta-pdt-plame/r08/(:num)', 'PdtPlame::consultaR08/$1');
$routes->get('descargarR08All/(:num)', 'PdtPlame::descargarR08All/$1');
$routes->post('rectificar-pdt-plame', 'PdtPlame::rectificarPlame');
$routes->post('rectificar-pdt-plame/r08', 'PdtPlame::rectificarR08');
$routes->get('eliminar-pdt-plame/r08/(:num)', 'PdtPlame::eliminarR08/$1');
$routes->get('eliminar-pdt-plame/(:num)/(:num)', 'PdtPlame::eliminar/$1/$2');
$routes->post('eliminar-pdt-plame/r08/all', 'PdtPlame::eliminarAll');
$routes->get('notificacion-pdt-plame', 'PdtPlame::notificacionPdtPlame');
$routes->get('pdf-view-r08', 'PdtPlame::pdfViewR08');
$routes->get('lista-boletas-pagos', 'PdtPlame::leerBoletaPago');

$routes->get('declaraciones/pdt-anual', 'PdtAnual::index');
$routes->get('pdtAnual/verificar/(:num)', 'PdtAnual::verificar/$1');
$routes->post('pdtAnual-consulta', 'PdtAnual::consulta');
$routes->post('pdtAnual/getBalance', 'PdtAnual::getBalance');
$routes->post('pdtAnual/guardar', 'PdtAnual::guardar');
$routes->post('pdtAnual/rectificar', 'PdtAnual::rectificar');
$routes->get('pdtAnual/delete/(:num)/(:num)', 'PdtAnual::deleteAnual/$1/$2');

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

$routes->get('all-sedes', 'Sede::show');

$routes->get('permisos', 'Permisos::index');
$routes->get('permisos-perfil/(:num)', 'Permisos::show/$1');
$routes->post('save-permisos', 'Permisos::guardar');
$routes->get('menu-nav', 'Permisos::permisos_menu');
$routes->get('generar-facturas', 'Permisos::generarFacturas');
$routes->get('perfiles', 'Permisos::listProfiles');
$routes->post('save-perfil', 'Permisos::savePerfil');
$routes->get('delete-perfil/(:num)', 'Permisos::deletePerfil/$1');

$routes->get('declaracion', 'Declaracion::index');
$routes->get('listaDeclaracion/(:num)', 'Declaracion::listaDeclaracion/$1');
$routes->post('declaracion/calendario', 'Declaracion::calendario');
$routes->post('declaracion/extraer_data', 'Declaracion::extraer_data');
$routes->post('declaracion/guardar_datos', 'Declaracion::guardar_datos');

$routes->get('empresas-notificacion', 'Api\Notificaciones::index');
$routes->get('mensajes-pendientes', 'Api\Notificaciones::mensajesPendientes');
$routes->post('update-mensaje', 'Api\Notificaciones::updateMessage');
$routes->get('send-email', 'Api\Notificaciones::sendEmail');
$routes->post('api/send-factura', 'Api\Notificaciones::sendFacturas');
$routes->get('api/listEmpresas', 'Api\Notificaciones::listEmpresas');
$routes->post('api/saveHonorario', 'Api\Notificaciones::saveHonorario');
$routes->post('api/save-factura', 'Api\Notificaciones::saveFactura');
$routes->post('api/excluir-periodo-pdt-renta', 'Api\Notificaciones::excluirPeriodoPdtRenta');
$routes->get('api/getMontos', 'Api\Notificaciones::getMontosPdtRenta');
$routes->post('api/excluir-periodo-pdt-plame', 'Api\Notificaciones::excluirPeriodoPdtPlame');
$routes->get('api/envioNotaCredito/(:num)', 'Facturas::getIdFactura/$1');

$routes->get('api/notificacion-afp', 'Api\Notificaciones::notificacionAfp');
$routes->get('api/insert-fecha_declaracion_afp', 'Api\Notificaciones::insert_fecha_declaracion_afp');

$routes->get('listaHonorarioFacturas/(:num)', 'Api\Notificaciones::getFacturasHonorarios/$1');
$routes->post('sendNotaCredito', 'Api\Notificaciones::sendApiEnviarNotaCredito');

$routes->get('api/insert-tipo-cambio', 'Api\Notificaciones::getCambios');
$routes->get('api/insert-tipo-cambio-facturador', 'Api\Notificaciones::getCambiosFacturador');
$routes->get('api/consulta-tipo-cambio/(:any)', 'Api\Notificaciones::getConsultaTipoCambio/$1');
$routes->get('api/get-contribuyentes-servidor', 'Api\Notificaciones::savePagoServidor');
$routes->post('api/generar-nota-venta-servidor', 'Api\Notificaciones::sendNotaVenta');
$routes->get('api/get-contribuyentes-servidor-ahora', 'Api\Notificaciones::savePagoServidorAhora');

$routes->post('api/update-nota-pago-servidor', 'Api\Notificaciones::updatePagoServidorNotaEnviada');

$routes->get('api/pagos-anuales-pendientes', 'Api\Notificaciones::renderPdtAnualesFacturas');
$routes->post('api/update-pagos-anuales', 'Api\Notificaciones::updatePagoAnual');
$routes->get('api/read-boletas-pago', 'Api\Notificaciones::readBoletasPago');
$routes->post('api/save-boleta-pago', 'Api\Notificaciones::saveDataBoletasPago');

$routes->get('mensajes-masivos', 'Mensajes::index');
$routes->post('mensajes/guardarMensajeMasivo', 'Mensajes::guardarMensajeMasivo');
$routes->get('lista-mensajes', 'Mensajes::listaMensajes');
$routes->get('all-mensaje', 'Mensajes::mensajesAll');
$routes->get('mensajes-all-id/(:num)', 'Mensajes::mensajesAllId/$1');
$routes->get('eliminar-mensaje/(:num)', 'Mensajes::delete/$1');

$routes->get('numeros-whatsapp', 'NumeroWhatsapp::index');
$routes->get('configuracion/numeroWhatsapp/all', 'NumeroWhatsapp::allNumeroWhatsapp');
$routes->post('configuracion/numeroWhatsapp/store', 'NumeroWhatsapp::saveNumeroWhatsapp');
$routes->get('configuracion/numeroWhatsapp/(:num)', 'NumeroWhatsapp::getIdNumeroWhatsapp/$1');
$routes->get('configuracion/deleteWhatsapp/(:num)', 'NumeroWhatsapp::deleteWhatsapp/$1');

$routes->get('facturas', 'Facturas::index');
$routes->get('facturas/listar-periodo', 'Facturas::listarFacturasPeriodo');
$routes->get('facturas/ver/(:num)', 'Facturas::facturasLista/$1');

$routes->get('enviarCorreo', 'Correo::enviar');

$routes->get('/reporte-ventas/(:any)', 'Ventas::buscarPorRuc/$1');
$routes->post('/sucursales', 'Ventas::sucursales');
$routes->post('/reporte-detallado', 'Ventas::ventaDetallada');
$routes->post('/maqueta-ventas', 'Ventas::maquetaVentas');

$routes->get('/sistema-control-produccion', 'Auth::produccion');
$routes->get('/sistema-ventas', 'Auth::sistemaventas');

$routes->get('/chat', 'Chat::index');
$routes->get('/chat-whatsapp', 'Chat::chatWhatsapp');

$routes->get('/afp', 'Afp::index');
$routes->post('/afp', 'Afp::save');
$routes->post('/consulta-afp', 'Afp::consulta');
$routes->post('/rectificar-afp', 'Afp::rectificar');
$routes->get('/afp/get-files-details/(:num)', 'Afp::getArchivos/$1');
$routes->get('/afp/delete/(:num)/(:num)', 'Afp::delete/$1/$2');
$routes->post('/consulta-afp-rango', 'Afp::consultaAfpRango');
$routes->get('/faltan-subir-afp', 'Afp::notificar_afp_all');
$routes->get('/afp/get-files-reporte/(:num)', 'Afp::getArchivosReporte/$1');
$routes->get('/afp/get-files-plantilla/(:num)', 'Afp::getArchivosPlantilla/$1');

$routes->get('/sire', 'Sire::index');
$routes->post('/sire', 'Sire::save');
$routes->post('/consulta-sire', 'Sire::consulta');
$routes->post('/rectificar-sire', 'Sire::rectificar');
$routes->get('/sire/get-files-details/(:num)', 'Sire::getArchivos/$1');
$routes->get('/sire/delete/(:num)/(:num)', 'Sire::delete/$1/$2');
$routes->post('/consulta-sire-rango', 'Sire::consultaSireRango');
$routes->get('/sire/files/(:num)', 'Sire::files/$1');
$routes->get('/sire/delete-file/(:num)', 'Sire::deleteFile/$1');
$routes->get('/notificar-sire', 'Sire::notificacionSireAll');
$routes->post('/excluir-periodo-sire', 'Sire::excluirPeriodo');

$routes->get('/customer-mypes', 'Pdt0621::mypes');
$routes->post('/customer-mypes-list', 'Pdt0621::listEmpresasMypes');
$routes->get('/customer-mypes-periodos/(:num)/(:num)', 'Pdt0621::listaPeriodosMypes/$1/$2');
$routes->get('/download-excel-mypes/(:num)/(:num)', 'Pdt0621::downloadExcelMypes/$1/$2');
$routes->get('excel/download-mypes/(:any)', 'Pdt0621::download/$1');
$routes->get('/update-estado-datos', 'Api\Notificaciones::actualizarMontosVentasComprasEstado');
$routes->post('pdt-0621/save-montos-mypes', 'Pdt0621::updateMontosMypes');

$routes->group('feriados', function ($routes) {
    $routes->get('/', 'Feriados::index'); // Todos
    $routes->get('fecha/(:segment)', 'Feriados::porFecha/$1');
    $routes->get('rango', 'Feriados::porRango');
    $routes->get('mes/(:num)/(:num)', 'Feriados::porMes/$1/$2');
    $routes->get('es-feriado/(:segment)', 'Feriados::esFeriado/$1');
});

$routes->get('/agenda', 'Agenda::index');
$routes->get('/agenda/getAgenda', 'Agenda::getAgenda');
$routes->post('/agenda/save', 'Agenda::save');
$routes->get('/agenda/actividades-hoy', 'Agenda::actividadesHoy');
$routes->get('/agenda/atendido-actividad-sin-evidencia/(:num)', 'Agenda::atendidoActividadSinEvidencia/$1');
$routes->post('/agenda/atendido-actividad-con-evidencia', 'Agenda::atendidoActividadConEvidencia');

$routes->group('api', function ($routes) {
    // Login público
    $routes->post('login', 'Api\Auth::login');
});

$routes->get('api/descargar-boleta/(:num)/(:num)', 'Api\AppUser::descargarPdfSellado/$1/$2');

// Rutas protegidas con JWT
$routes->group('api', ['filter' => 'jwt'], function ($routes) {
    $routes->post('empresas', 'Api\AppUser::empresas');
    $routes->get('getEmpresa/(:num)', 'Api\AppUser::getEmpresa/$1');
    $routes->get('anios', 'Api\AppUser::getAnios');
    $routes->post('lista_boletas', 'Api\AppUser::itemBoletas');
    $routes->post('upload-sello-firma', 'Api\AppUser::uploadSelloFirma');
    $routes->get('get-sello-firma/(:num)', 'Api\AppUser::getSelloFirma/$1');
    $routes->post('consulta-pdt-renta', 'Api\AppUser::consultaPdtRenta');
    $routes->post('consulta-pdt-plame', 'Api\AppUser::consultaPdtPlame');
    $routes->post('change-password', 'Api\AppUser::changePassword');
});
