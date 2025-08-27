<?php

namespace App\Controllers;

use App\Models\MetodoPagoModel;
use App\Models\TipoComprobanteModel;
use App\Models\ContribuyenteModel;
use App\Models\HistorialTarifaModel;
use App\Models\PagosModel;
use App\Models\PagosHonorariosModel;
use App\Models\ContratosModel;
use App\Models\MovimientoModel;
use App\Models\DetallePagosModel;
use App\Models\SistemaContribuyenteModel;
use App\Models\ServidorModel;
use App\Models\PagoAmortizacionServidorModel;
use App\Models\PagoServidorModel;
use App\Models\DetallePagosServidorModel;

use DateTime;

class Pago extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('pagos/index', compact('menu'));
    }

    public function pagosHonorarios($id)
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $contri = new ContribuyenteModel();
        $pagos = new PagosModel();

        $datos = $contri->find($id);

        $metodo = new MetodoPagoModel();
        $metodos = $metodo->where('estado', 1)->findAll();

        $tarifa = new HistorialTarifaModel();

        //$monto_mensual = $this->getMontoMensual($id);

        $tipoComprobante = new TipoComprobanteModel();
        $tipos = $tipoComprobante->where('tipo_comprobante_estado', 1)->findAll();

        $fechaActual = new DateTime();

        // Restar 3 días
        $fechaActual->modify('-3 days');

        // Formatear la fecha al formato deseado
        $fechaRestada = $fechaActual->format('Y-m-d');

        $verificarPago = $pagos->where('contribuyente_id', $id)->where('estado !=', 'eliminado')->orderBy('id', 'desc')->findAll();

        $countPagos = "";

        if ($verificarPago) {
            $countPagos = 1;
        } else {
            $countPagos = 0;
        }

        $menu = $this->permisos_menu();

        return view('pagos/pagar', compact('id', 'metodos', 'tipos', 'datos', 'fechaRestada', 'countPagos', 'menu'));
    }

    public function getMontoPendiente($id)
    {
        $pagos = new PagosModel();
        $contri = new ContribuyenteModel();

        $datos = $contri->find($id);

        $verificarPago = $pagos->where('contribuyente_id', $id)->where('estado !=', 'eliminado')->orderBy('id', 'desc')->findAll();

        if ($verificarPago) {
            if ($verificarPago[0]['estado'] == "pendiente") {
                $montoPagar = $verificarPago[0]['montoPendiente'];
            } else {

                $dt = DateTime::createFromFormat('Y-m-d', $verificarPago[0]['mesCorrespondiente']);
                $dt->modify('first day of this month');
                $dt->modify('+1 month');

                $periodo = $dt->format('Y-m');

                $mesCorrespondiente = $this->obtenerFechaValidaDeCobro($periodo, $datos['diaCobro']);

                $montoMensual = $this->getMontoMensualHistorial($id, $mesCorrespondiente);

                $montoPagar = $montoMensual;
            }
        } else {
            $montoPagar = $datos['costoMensual'];
        }

        return $this->response->setJSON([
            "status" => "success",
            "montoPagar" => $montoPagar
        ]);
    }

    public function montoServidor($id)
    {
        $servidor = new ServidorModel();

        $verificar = $servidor->where('contribuyente_id', $id)->where('estado', 1)->first();

        if (!$verificar) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Falta configurar su monto para el pago del servidor"
            ]);
        }

        return $this->response->setJSON([
            "status" => "success",
            "monto" => $verificar['monto']
        ]);
    }

    public function listaPagos($id)
    {
        $pago = new PagosModel();

        $pago->query("SET lc_time_names = 'es_ES'");

        $pagos = $pago->query("SELECT p.contribuyente_id, DATE_FORMAT(p.fecha_pago , '%d-%m-%Y') as fecha_pago, DATE_FORMAT(p.fecha_proceso , '%d-%m-%Y') as fecha_proceso, DATE_FORMAT(p.mesCorrespondiente, '%M-%Y') as mesCorrespondiente, p.monto_total, p.montoPagado, p.montoPendiente, p.montoExcedente, p.estado, p.fecha_pago as fechaPago from pagos p where p.contribuyente_id = $id and p.estado != 'eliminado' order by p.id desc")->getResult();

        return $this->response->setJSON($pagos);
    }

    public function listaPagosHonorarios($id)
    {
        $pago = new PagosHonorariosModel();
        $detallePagos = new DetallePagosModel();

        $pago->query("SET lc_time_names = 'es_ES'");

        $pagos = $pago->query("SELECT p.id, p.contribuyente_id, DATE_FORMAT(p.fecha_pago , '%d-%m-%Y') as fecha_pago, DATE_FORMAT(p.registro, '%d-%m-%Y %H-%i-%s') as registro, p.fecha, p.monto, p.estado, p.voucher, mp.metodo from pagos_honorarios p INNER JOIN metodos_pagos mp ON mp.id = p.metodo_pago_id where p.contribuyente_id = $id and p.estado = 1 order by p.id desc")->getResult();

        foreach ($pagos as $key => $value) {
            $id = $value->id;

            $detalle = $detallePagos->query("SELECT pa.monto, DATE_FORMAT(p.mesCorrespondiente, '%M-%Y') as mesCorrespondiente FROM pagos_amortizaciones as pa INNER JOIN pagos as p ON p.id = pa.pago_id WHERE pa.honorario_id = $id")->getResult();

            $pagos[$key]->pagos = $detalle;
        }

        return $this->response->setJSON($pagos);
    }

    public function verificarSistemaContribuyente($id)
    {
        $sistemaContribuyente = new SistemaContribuyenteModel();

        $verificar = $sistemaContribuyente->where('contribuyente_id', $id)->where('system_id !=', 3)->findAll();

        if ($verificar) {
            return $verificar;
        }

        return [];
    }

    public function activarSuscripcion($sistemas, $ruc)
    {
        foreach ($sistemas as $key => $value) {
            if ($value['system_id'] == 1) {
                $traer_rest = $this->getSchemasRestaurantes($ruc);

                $fecha_suscripcion = $traer_rest['datos'][0]['seco_fecha_vencimiento_suscripcion'];

                $this->updatePagoRestaurante($traer_rest['datos'], $traer_rest['schemaName'], $fecha_suscripcion, 1);
            }

            if ($value['system_id'] == 2) {
                $traer_rest = $this->contribuyentesEsFacturador($ruc);

                $fecha_suscripcion = $traer_rest['fecha_expiracion'];

                $dt = new DateTime($fecha_suscripcion);
                $fechaCorrecta = $dt->format('Y-m-d');

                $this->updateVencimientoFacturador($ruc, $fechaCorrecta, 1);
            }
        }
    }

    public function pagarHonorario()
    {
        $pago = new PagosModel();
        $contrib = new ContribuyenteModel();
        $paHono = new PagosHonorariosModel();

        $detallePagos = new DetallePagosModel();

        try {
            $pago->db->transBegin();

            $idContribuyente = $this->request->getvar('idcontribuyente');
            $metodoPago = $this->request->getvar('metodoPago');
            $monto = $this->request->getvar('monto');
            $diaCobro = $this->request->getvar('diaCobro');
            $fecha_proceso = $this->request->getvar('fecha_proceso');

            $dataContrib = $contrib->where('id', $idContribuyente)->first();

            $sistemas = $this->verificarSistemaContribuyente($idContribuyente);

            $nameFile = "";

            if ($metodoPago != 1) {
                $voucher = $this->request->getFile('voucher');

                if ($voucher->isValid() && !$voucher->hasMoved()) {
                    $newName = $voucher->getRandomName();
                    $voucher->move(FCPATH . 'vouchers', $newName);

                    $nameFile = $newName;
                }

                $id_sede = "";
            } else {
                $id_sede = $this->request->getvar('selectSede');
            }

            $montoMensual = $dataContrib['costoMensual'];

            $idPagoHonorario = 0;

            if (isset($_POST['generarMovimiento'])) {

                $dataSede = $this->Aperturar($metodoPago, $id_sede);

                if ($metodoPago == 1) {
                    $sesionId = $dataSede['idSesionFisica'];

                    $iduser = $dataSede['idUser'];
                } else {
                    $sesionId = $dataSede['idSesionVirtual'];

                    $iduser = session()->id;
                }

                $descripcion = "Pago de Honorario de " . $dataContrib['razon_social'];

                $idMovimiento = $this->generarMovimiento($sesionId, 1, 1, $metodoPago, $monto, $descripcion, 5, 'TICKET - 0001', 1, $fecha_proceso, $nameFile, $iduser);

                $data_honorario = array(
                    "contribuyente_id" => $idContribuyente,
                    "movimientoId" => $idMovimiento,
                    "registro" => date('Y-m-d H:i:s'),
                    "fecha" => date('Y-m-d'),
                    "fecha_pago" => $fecha_proceso,
                    "metodo_pago_id" => $metodoPago,
                    "monto" => $monto,
                    "voucher" => $nameFile,
                    "estado" => 1
                );

                $paHono->insert($data_honorario);

                $idPagoHonorario = $paHono->getInsertID();
            }

            $contador = 0;

            if (isset($_POST['periodo'])) {
                //$periodo = $this->request->getvar('periodo') . "-" . $diaCobro;

                $fecha_valida = $this->obtenerFechaValidaDeCobro($this->request->getvar('periodo'), $diaCobro);

                $montoMensual = $this->getMontoMensualHistorial($idContribuyente, $fecha_valida);

                if ($monto >= $montoMensual) {
                    $estado = "pagado";
                    $newMonto = $montoMensual;
                    $pendientePago = 0;

                    $contador++;
                } else {
                    $estado = "pendiente";
                    $newMonto = $monto;
                    $pendientePago = $montoMensual - $monto;
                }

                $data = array(
                    "contribuyente_id" => $idContribuyente,
                    "fecha_pago" => date('Y-m-d H:i:s'),
                    "fecha_proceso" => $fecha_proceso,
                    "monto_total" => $montoMensual,
                    "mesCorrespondiente" => $fecha_valida,
                    "montoPagado" => $newMonto,
                    "montoPendiente" => $pendientePago,
                    "montoExcedente" => 0,
                    "usuario_id_cobra" => session()->id,
                    "estado" => $estado,
                );

                $pago->insert($data);

                $datosPagos = array(
                    "pago_id" => $pago->getInsertID(),
                    "honorario_id" => $idPagoHonorario,
                    "monto" => $newMonto,
                );

                $detallePagos->insert($datosPagos);

                $monto = $monto - $montoMensual;
            }

            $returnPagos = $this->addPagos($monto, $idContribuyente, $idPagoHonorario, $diaCobro, $fecha_proceso, $contador, $sistemas, $dataContrib['ruc']);

            if ($pago->db->transStatus() === false) {
                $pago->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $pago->db->transCommit();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente el pago"
            ]);
        } catch (\Exception $e) {
            $pago->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function pagarServidor()
    {
        $pagoServidor = new PagoServidorModel();
        $contrib = new ContribuyenteModel();
        $paAmor = new PagoAmortizacionServidorModel();

        $detallePagos = new DetallePagosServidorModel();

        try {
            $pagoServidor->db->transBegin();

            $idContribuyente = $this->request->getvar('idcontribuyente');
            $metodoPago = $this->request->getvar('metodoPago');
            $monto = $this->request->getvar('monto');
            $diaCobro = $this->request->getvar('diaCobro');
            $fecha_proceso = $this->request->getvar('fecha_proceso');

            $dataContrib = $contrib->where('id', $idContribuyente)->first();

            if ($metodoPago != 1) {
                $voucher = $this->request->getFile('voucher');

                if ($voucher->isValid() && !$voucher->hasMoved()) {
                    $newName = $voucher->getRandomName();
                    $voucher->move(FCPATH . 'servidor', $newName);

                    $nameFile = $newName;
                }

                $id_sede = "";
            } else {
                $id_sede = $this->request->getvar('selectSede');
            }

            $idPagoAmor = 0;

            if (isset($_POST['generarMovimiento'])) {

                $dataSede = $this->Aperturar($metodoPago, $id_sede);

                if ($metodoPago == 1) {
                    $sesionId = $dataSede['idSesionFisica'];

                    $iduser = $dataSede['idUser'];
                } else {
                    $sesionId = $dataSede['idSesionVirtual'];

                    $iduser = session()->id;
                }

                $descripcion = "Pago de Honorario de " . $dataContrib['razon_social'];

                $idMovimiento = $this->generarMovimiento($sesionId, 1, 1, $metodoPago, $monto, $descripcion, 5, 'TICKET - 0001', 1, $fecha_proceso, $nameFile, $iduser);

                $data_honorario = array(
                    "contribuyente_id" => $idContribuyente,
                    "movimientoId" => $idMovimiento,
                    "registro" => date('Y-m-d H:i:s'),
                    "fecha" => date('Y-m-d'),
                    "fecha_pago" => $fecha_proceso,
                    "metodo_pago_id" => $metodoPago,
                    "monto" => $monto,
                    "voucher" => $nameFile,
                    "estado" => 1
                );

                $paAmor->insert($data_honorario);

                $idPagoAmor = $paAmor->getInsertID();
            }

            if (isset($_POST['periodo'])) {
                //$periodo = $this->request->getvar('periodo') . "-" . $diaCobro;

                $fecha_valida = $this->request->getvar('periodo');

                $montoMensual = $this->getMontoServidor($idContribuyente);

                if ($monto >= $montoMensual) {
                    $estado = "pagado";
                    $newMonto = $montoMensual;
                    $pendientePago = 0;
                } else {
                    $estado = "pendiente";
                    $newMonto = $monto;
                    $pendientePago = $montoMensual - $monto;
                }

                $data = array(
                    "contribuyente_id" => $idContribuyente,
                    "fecha_pago" => date('Y-m-d H:i:s'),
                    "fecha_proceso" => $fecha_proceso,
                    "monto_total" => $montoMensual,
                    "anio_correspondiente" => $fecha_valida,
                    "montoPagado" => $newMonto,
                    "montoPendiente" => $pendientePago,
                    "montoExcedente" => 0,
                    "usuario_id_cobra" => session()->id,
                    "estado" => $estado,
                );

                $pagoServidor->insert($data);

                $datosPagos = array(
                    "pago_servidor_id" => $pagoServidor->getInsertID(),
                    "pago_amortizacion_id" => $idPagoAmor,
                    "monto" => $newMonto,
                );

                $detallePagos->insert($datosPagos);

                $monto = $monto - $montoMensual;
            }

            $return_pagos = $this->addPagosServidor($monto, $idContribuyente, $idPagoAmor, $fecha_proceso);

            if ($pagoServidor->db->transStatus() === false) {
                $pagoServidor->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $pagoServidor->db->transCommit();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente el pago"
            ]);
        } catch (\Exception $e) {
            $pagoServidor->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    function obtenerFechaValidaDeCobro($periodo, $diaDeseado)
    {
        // Concatenamos la fecha
        $fechaString = $periodo . '-' . str_pad($diaDeseado, 2, '0', STR_PAD_LEFT);

        // Intentamos crear la fecha
        $fecha = DateTime::createFromFormat('Y-m-d', $fechaString);

        // Verificamos si la fecha es válida (coincide con lo que queríamos crear)
        if ($fecha && $fecha->format('Y-m-d') === $fechaString) {
            return $fecha->format('Y-m-d'); // Fecha válida
        } else {
            // No existe ese día en el mes, así que buscamos el último día del mes
            $fechaBase = DateTime::createFromFormat('Y-m', $periodo);
            if (!$fechaBase) return null; // Periodo inválido

            $fechaBase->modify('last day of this month');
            return $fechaBase->format('Y-m-d');
        }
    }

    public function addPagos($monto, $idContribuyente, $idPagoHonorario, $diaCobro, $fecha_proceso, $contador, $sistemas = null, $ruc = null)
    {
        $pago = new PagosModel();
        $detallePagos = new DetallePagosModel();

        $montoDisponible = $monto;

        while ($montoDisponible > 0) {
            $lastUtimo = $pago->query("SELECT * FROM pagos WHERE contribuyente_id = $idContribuyente and estado != 'eliminado' ORDER BY id DESC LIMIT 1")->getRow();

            if ($lastUtimo->estado == "pendiente") {
                $montoPendiente = $lastUtimo->montoPendiente;
                $montoPagado = $lastUtimo->montoPagado;
                $montoTotal = $lastUtimo->monto_total;

                if ($montoDisponible >= $montoPendiente) {
                    $datos = array(
                        "montoPagado" => $montoTotal,
                        "montoPendiente" => 0,
                        "montoExcedente" => 0,
                        "estado" => "pagado"
                    );

                    $pago->update($lastUtimo->id, $datos);

                    $datosPagos = array(
                        "pago_id" => $lastUtimo->id,
                        "honorario_id" => $idPagoHonorario,
                        "monto" => $montoPendiente,
                    );

                    $detallePagos->insert($datosPagos);

                    $montoDisponible = $montoDisponible - $montoPendiente;

                    if (count($sistemas) > 0) {
                        $this->activarSuscripcion($sistemas, $ruc);
                    }

                    $contador++;
                } else {
                    $datos = array(
                        "montoPagado" => $montoPagado + $montoDisponible,
                        "montoPendiente" => $montoPendiente - $montoDisponible,
                        "montoExcedente" => 0,
                        "estado" => "pendiente"
                    );

                    $pago->update($lastUtimo->id, $datos);

                    $datosPagos = array(
                        "pago_id" => $lastUtimo->id,
                        "honorario_id" => $idPagoHonorario,
                        "monto" => $montoDisponible,
                    );

                    $detallePagos->insert($datosPagos);

                    $montoDisponible = 0;
                }
            } else {
                $dt = DateTime::createFromFormat('Y-m-d', $lastUtimo->mesCorrespondiente);
                $dt->modify('first day of this month');
                $dt->modify('+1 month');

                $periodo = $dt->format('Y-m');

                $mesCorrespondiente = $this->obtenerFechaValidaDeCobro($periodo, $diaCobro);

                $montoMensual = $this->getMontoMensualHistorial($idContribuyente, $mesCorrespondiente);

                if ($montoDisponible >= $montoMensual) {
                    $datos = array(
                        "contribuyente_id" => $idContribuyente,
                        "mesCorrespondiente" => $mesCorrespondiente,
                        "monto_total" => $montoMensual,
                        "fecha_pago" => date('Y-m-d H:i:s'),
                        "fecha_proceso" => $fecha_proceso,
                        "montoPagado" => $montoMensual,
                        "montoPendiente" => 0,
                        "montoExcedente" => 0,
                        "estado" => "pagado",
                        "usuario_id_cobra" => session()->id
                    );

                    $pago->insert($datos);

                    $montoDisponible = $montoDisponible - $montoMensual;

                    $datosPagos = array(
                        "pago_id" => $pago->getInsertID(),
                        "honorario_id" => $idPagoHonorario,
                        "monto" => $montoMensual,
                    );

                    $detallePagos->insert($datosPagos);

                    if (count($sistemas) > 0) {
                        $this->activarSuscripcion($sistemas, $ruc);
                    }

                    $contador++;
                } else {
                    $datos = array(
                        "contribuyente_id" => $idContribuyente,
                        "mesCorrespondiente" => $mesCorrespondiente,
                        "fecha_pago" => date('Y-m-d H:i:s'),
                        "fecha_proceso" => $fecha_proceso,
                        "monto_total" => $montoMensual,
                        "montoPagado" => $montoDisponible,
                        "montoPendiente" => $montoMensual - $montoDisponible,
                        "montoExcedente" => 0,
                        "estado" => "pendiente",
                        "usuario_id_cobra" => session()->id
                    );

                    $pago->insert($datos);

                    $datosPagos = array(
                        "pago_id" => $pago->getInsertID(),
                        "honorario_id" => $idPagoHonorario,
                        "monto" => $montoDisponible,
                    );

                    $detallePagos->insert($datosPagos);

                    $montoDisponible = 0;
                }
            }
        }

        $empr = new ContribuyenteModel();
        $dataUpdate = array(
            "cantidadPagos" => $contador
        );

        $empr->update($idContribuyente, $dataUpdate);
    }

    public function addPagosServidor($monto, $idContribuyente, $idPagoAmor, $fecha_proceso)
    {
        $pago = new PagoServidorModel();
        $detallePagos = new DetallePagosServidorModel();

        $montoDisponible = $monto;

        while ($montoDisponible > 0) {
            $lastUtimo = $pago->query("SELECT * FROM pago_servidor WHERE contribuyente_id = $idContribuyente and estado != 'eliminado' ORDER BY id DESC LIMIT 1")->getRow();

            if ($lastUtimo->estado == "pendiente") {
                $montoPendiente = $lastUtimo->montoPendiente;
                $montoPagado = $lastUtimo->montoPagado;
                $montoTotal = $lastUtimo->monto_total;

                if ($montoDisponible >= $montoPendiente) {
                    $datos = array(
                        "montoPagado" => $montoTotal,
                        "montoPendiente" => 0,
                        "montoExcedente" => 0,
                        "estado" => "pagado"
                    );

                    $pago->update($lastUtimo->id, $datos);

                    $datosPagos = array(
                        "pago_servidor_id" => $lastUtimo->id,
                        "pago_amortizacion_id" => $idPagoAmor,
                        "monto" => $montoPendiente,
                    );

                    $detallePagos->insert($datosPagos);

                    $montoDisponible = $montoDisponible - $montoPendiente;
                } else {
                    $datos = array(
                        "montoPagado" => $montoPagado + $montoDisponible,
                        "montoPendiente" => $montoPendiente - $montoDisponible,
                        "montoExcedente" => 0,
                        "estado" => "pendiente"
                    );

                    $pago->update($lastUtimo->id, $datos);

                    $datosPagos = array(
                        "pago_servidor_id" => $lastUtimo->id,
                        "pago_amortizacion_id" => $idPagoAmor,
                        "monto" => $montoDisponible,
                    );

                    $detallePagos->insert($datosPagos);

                    $montoDisponible = 0;
                }
            } else {
                $dt = DateTime::createFromFormat('Y-m-d', $lastUtimo->anio_correspondiente);
                $dt->modify('first day of this month');
                $dt->modify('+1 year');

                $periodo = $dt->format('Y-m-d');

                $anio_correspondiente = $periodo;

                $montoMensual = $this->getMontoServidor($idContribuyente);

                if ($montoDisponible >= $montoMensual) {
                    $datos = array(
                        "contribuyente_id" => $idContribuyente,
                        "anio_correspondiente" => $anio_correspondiente,
                        "monto_total" => $montoMensual,
                        "fecha_pago" => date('Y-m-d H:i:s'),
                        "fecha_proceso" => $fecha_proceso,
                        "montoPagado" => $montoMensual,
                        "montoPendiente" => 0,
                        "montoExcedente" => 0,
                        "estado" => "pagado",
                        "usuario_id_cobra" => session()->id
                    );

                    $pago->insert($datos);

                    $montoDisponible = $montoDisponible - $montoMensual;

                    $datosPagos = array(
                        "pago_servidor_id" => $pago->getInsertID(),
                        "pago_amortizacion_id" => $idPagoAmor,
                        "monto" => $montoMensual,
                    );

                    $detallePagos->insert($datosPagos);
                } else {
                    $datos = array(
                        "contribuyente_id" => $idContribuyente,
                        "anio_correspondiente" => $anio_correspondiente,
                        "fecha_pago" => date('Y-m-d H:i:s'),
                        "fecha_proceso" => $fecha_proceso,
                        "monto_total" => $montoMensual,
                        "montoPagado" => $montoDisponible,
                        "montoPendiente" => $montoMensual - $montoDisponible,
                        "montoExcedente" => 0,
                        "estado" => "pendiente",
                        "usuario_id_cobra" => session()->id
                    );

                    $pago->insert($datos);

                    $datosPagos = array(
                        "pago_servidor_id" => $pago->getInsertID(),
                        "pago_amortizacion_id" => $idPagoAmor,
                        "monto" => $montoDisponible,
                    );

                    $detallePagos->insert($datosPagos);

                    $montoDisponible = 0;
                }
            }
        }
    }

    public function getMontoServidor($idContribuyente)
    {
        $servidor = new ServidorModel();

        $data = $servidor->query("SELECT monto FROM servidor WHERE contribuyente_id = $idContribuyente and estado = 1")->getRow();

        return $data->monto;
    }

    public function getMontoMensualHistorial($id, $fecha)
    {
        $contrib = new ContribuyenteModel();

        $data = $contrib->query("SELECT c.contribuyenteId, ht.fecha_inicio, ht.monto_mensual
        FROM historial_tarifas ht
        INNER JOIN contratos c ON ht.contratoId = c.id
        WHERE c.contribuyenteId = $id AND c.estado = 1 AND ht.fecha_inicio <= '$fecha' and ht.estado = 1 ORDER BY ht.fecha_inicio DESC;")->getResult();

        if ($data) {
            return $data[0]->monto_mensual;
        }

        $data = $contrib->query("SELECT c.contribuyenteId, ht.fecha_inicio, ht.monto_mensual
        FROM historial_tarifas ht
        INNER JOIN contratos c ON ht.contratoId = c.id
        WHERE c.contribuyenteId = $id AND c.estado = 1 and ht.estado = 1 ORDER BY ht.fecha_inicio DESC;")->getResult();

        return $data[0]->monto_mensual;
    }

    public function historialPagos($id)
    {
        $contrib = new ContribuyenteModel();

        $contrib->query("SET lc_time_names = 'es_ES'");

        $data = $contrib->query("SELECT c.id, c.contribuyenteId, DATE_FORMAT(ht.fecha_inicio, '%M-%Y') as fechaInicio, ht.fecha_fin, ht.monto_mensual, ht.monto_anual, ht.estado
        FROM historial_tarifas ht
        INNER JOIN contratos c ON ht.contratoId = c.id
        WHERE c.contribuyenteId = $id and ht.estado = 1 ORDER BY ht.fecha_inicio DESC;")->getResult();

        return $this->response->setJSON($data);
    }

    public function renderPagos()
    {
        $contrib = new ContribuyenteModel();

        $data = $contrib->query("SELECT * FROM contribuyentes as c INNER JOIN pagos as p ON p.contribuyente_id = c.id INNER JOIN metodos_pagos as mp ON mp.id = p.metodo_pago_id WHERE p.estado = 'pendiente'")->getResult();

        return $this->response->setJSON($data);
    }

    public function insertContratos()
    {
        $contrib = new ContribuyenteModel();
        $historial = new HistorialTarifaModel();

        $contrib->db->transStart();

        try {
            $data = $contrib->where('estado !=', 0)->findAll();

            foreach ($data as $key => $value) {
                $contrato = new ContratosModel();

                $fechaContrato = $value['fechaContrato'];

                $datos = [
                    "contribuyenteId" => $value['id'],
                    "fechaInicio" => $fechaContrato,
                    "fechaFin" => "",
                    "diaCobro" => $value['diaCobro'],
                    "estado" => 1
                ];

                $contrato->insert($datos);

                $id = $contrato->getInsertID();

                $history = $historial->where('contribuyente_id', $value['id'])->orderBy('id', 'ASC')->findAll();

                if ($history) {
                    foreach ($history as $keys => $values) {
                        $dato = [
                            "contratoId" => $id,
                        ];

                        $historial->update($values['id'], $dato);
                    }
                }
            }

            $contrib->db->transComplete();

            if ($contrib->db->transStatus() === false) {
                throw new \Exception("Error al realizar la operación.");
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente"
            ]);
        } catch (\Exception $e) {
            $contrib->db->transRollback();

            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deletePago($id)
    {
        $pago = new PagosModel();
        $pagoHo = new PagosHonorariosModel();
        $mov = new MovimientoModel();
        $detalle = new DetallePagosModel();
        $contri = new ContribuyenteModel();

        $pago->db->transStart();

        try {
            $data = $pagoHo->find($id);

            $contribId = $data['contribuyente_id'];
            $monto = $data['monto'];
            $moviId = $data['movimientoId'];

            $dataContr = $contri->select('ruc, cantidadPagos')->where('id', $contribId)->first();

            $mov->update($moviId, ['mov_estado' => 0]);

            $pagoHo->update($id, ['estado' => 0]);

            $detalle->where('honorario_id', $id)->delete();

            $this->deletePagoArray($contribId, $monto);

            $ruc = $dataContr['ruc'];
            $contador = $dataContr['cantidadPagos'];

            $sistemas = $this->verificarSistemaContribuyente($contribId);

            if (count($sistemas) > 0) {
                $this->regresarFechaSuscripcion($contador, $sistemas, $ruc);
            }

            $pago->db->transComplete();

            if ($pago->db->transStatus() === false) {
                throw new \Exception("Error al realizar la operación.");
            }

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se elimino correctamente"
            ]);
        } catch (\Exception $e) {
            $pago->db->transRollback();

            return $this->response->setJSON([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function updateVaucher()
    {
        $pago = new PagosHonorariosModel();

        try {
            $idPago = $this->request->getVar('idPago');
            $voucher = $this->request->getFile('imagenVoucher');

            $dataPago = $pago->find($idPago);

            $nameFileDelete = $dataPago['voucher'];

            $filePath = FCPATH . 'vouchers/' . $nameFileDelete;

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $nameFile = "";

            if ($voucher->isValid() && !$voucher->hasMoved()) {
                $newName = $voucher->getRandomName();
                $voucher->move(FCPATH . 'vouchers', $newName);

                $nameFile = $newName;
            }

            $data = [
                "voucher" => $nameFile
            ];

            $pago->update($idPago, $data);

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getPago($id)
    {
        $pago = new PagosHonorariosModel();

        $data = $pago->find($id);

        return $this->response->setJSON($data);
    }

    public function updatePago()
    {
        $pagoHono = new PagosHonorariosModel();
        $mov = new MovimientoModel();
        $pago = new PagosModel();
        $contri = new ContribuyenteModel();
        $detalle = new DetallePagosModel();

        try {
            $pagoHono->db->transBegin();

            $id = $this->request->getVar('id_Pago');
            $monto = $this->request->getVar('monto_mov');
            $datePago = $this->request->getVar('datePago');
            $metodo_pago = $this->request->getVar('metodo_pago');
            $montoActual = $this->request->getVar('montoActual');

            $dataPago = $pagoHono->find($id);

            $contribId = $dataPago['contribuyente_id'];

            $dataContrib = $contri->find($contribId);

            $montoMensual = $dataContrib['costoMensual'];
            $diaCobro = $dataContrib['diaCobro'];
            $cantidadPagos = $dataContrib['cantidadPagos'];
            $ruc = $dataContrib['ruc'];

            $montoOriginal = $monto;

            $detalle->where('honorario_id', $id)->delete();

            $this->deletePagoArray($contribId, $montoActual);

            $thePagos = $pago->query("SELECT * FROM pagos WHERE contribuyente_id = $contribId and estado != 'eliminado' ORDER BY id ASC")->getResult();

            $thePagos_ = $pago->query("SELECT * FROM pagos WHERE contribuyente_id = $contribId ORDER BY id ASC")->getResult();

            if (!$thePagos) {
                $periodo = date('Y-m', strtotime($thePagos_[0]->mesCorrespondiente));
                $fecha_valida = $this->obtenerFechaValidaDeCobro($periodo, $diaCobro);

                $montoMensual = $this->getMontoMensualHistorial($contribId, $fecha_valida);

                if ($monto >= $montoMensual) {
                    $estado = "pagado";
                    $newMonto = $montoMensual;
                    $pendientePago = 0;
                } else {
                    $estado = "pendiente";
                    $newMonto = $monto;
                    $pendientePago = $montoMensual - $monto;
                }

                $data = array(
                    "contribuyente_id" => $contribId,
                    "fecha_pago" => date('Y-m-d H:i:s'),
                    "fecha_proceso" => $datePago,
                    "monto_total" => $montoMensual,
                    "mesCorrespondiente" => $fecha_valida,
                    "montoPagado" => $newMonto,
                    "montoPendiente" => $pendientePago,
                    "montoExcedente" => 0,
                    "usuario_id_cobra" => session()->id,
                    "estado" => $estado,
                );

                $pago->insert($data);

                $datosPagos = array(
                    "pago_id" => $pago->getInsertID(),
                    "honorario_id" => $id,
                    "monto" => $newMonto,
                );

                $detalle->insert($datosPagos);

                $monto = $monto - $montoMensual;
            }

            $sistemas = $this->verificarSistemaContribuyente($contribId);

            if (count($sistemas) > 0) {
                $this->regresarFechaSuscripcion($cantidadPagos, $sistemas, $ruc);
            }

            $contador = 0;

            $this->addPagos($monto, $contribId, $id, $diaCobro, $datePago, $contador, $sistemas, $dataContrib['ruc']);

            $dataPagoHono = [
                "monto" => $montoOriginal,
                "fecha_pago" => $datePago,
                "metodo_pago_id" => $metodo_pago
            ];

            $pagoHono->update($id, $dataPagoHono);

            $movId = $dataPago['movimientoId'];

            $dataMov = [
                "mov_monto" => $montoOriginal,
                "mov_fecha_pago" => $datePago,
                "id_metodo_pago" => $metodo_pago
            ];

            $mov->update($movId, $dataMov);

            if ($pagoHono->db->transStatus() === false) {
                $pagoHono->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $pagoHono->db->transCommit();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se guardo correctamente"
            ]);
        } catch (\Exception $e) {
            $pagoHono->db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function regresarFechaSuscripcion($contador, $sistemas, $ruc)
    {
        foreach ($sistemas as $key => $value) {
            if ($value['system_id'] == 1) {

                for ($i = 0; $i < $contador; $i++) {
                    $traer_rest = $this->getSchemasRestaurantes($ruc);

                    $fecha_suscripcion = $traer_rest['datos'][0]['seco_fecha_vencimiento_suscripcion'];

                    $this->updatePagoRestaurante($traer_rest['datos'], $traer_rest['schemaName'], $fecha_suscripcion, 0);
                }
            }

            if ($value['system_id'] == 2) {

                for ($i = 0; $i < $contador; $i++) {
                    $traer_rest = $this->contribuyentesEsFacturador($ruc);

                    $fecha_suscripcion = $traer_rest['fecha_expiracion'];

                    $this->updateVencimientoFacturador($ruc, $fecha_suscripcion, 0);
                }
            }
        }
    }
}
