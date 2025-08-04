<?php

namespace App\Controllers;

use App\Models\MetodoPagoModel;
use App\Models\TipoComprobanteModel;
use App\Models\MovimientoModel;
use App\Models\BancosModel;
use App\Models\PagosHonorariosModel;
use App\Models\DetallePagosModel;
use DateTime;

class Movimiento extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        $metodo = new MetodoPagoModel();
        $tipoComp = new TipoComprobanteModel();

        $metodos = $metodo->where('estado', 1)->findAll();
        $comprobantes = $tipoComp->where('tipo_comprobante_estado', 1)->findAll();

        return view('movimiento/cajero', compact('metodos', 'comprobantes', 'menu'));

        /*$sede = new SedeModel();

        $sedes = $sede->where('estado', 1)->findAll();

        return view('movimiento/index', compact('sedes', 'menu'));*/
    }

    public function guardar()
    {
        $mov = new MovimientoModel();

        try {

            $mov->db->transBegin();

            $idMovimiento = $this->request->getVar('idMovimiento');
            $tipo_movimiento = $this->request->getVar('tipo_movimiento');
            $conceptoCaja = $this->request->getVar('conceptoCaja');
            $metodoPago = $this->request->getVar('metodoPago');
            $descripcion = $this->request->getVar('descripcion');
            $monto = $this->request->getVar('monto');
            $comprobante = $this->request->getVar('comprobante');
            $serie = $this->request->getVar('serie');
            $numero = $this->request->getVar('correlativo');

            $dataSede = $this->Aperturar($metodoPago, session()->sede_id);

            $tipo_descripcion = $serie . "-" . $numero;

            $nameFile = "";

            if ($metodoPago == 1) {
                $idSesionCaja = $dataSede['idSesionFisica'];
            } else {
                $idSesionCaja = $dataSede['idSesionVirtual'];

                if ($metodoPago != 1) {
                    $vaucher = $this->request->getFile('vaucher');

                    if ($vaucher->isValid() && !$vaucher->hasMoved()) {
                        $newName = $vaucher->getRandomName();
                        $vaucher->move(FCPATH . 'vouchers', $newName);

                        $nameFile = $newName;
                    }
                }
            }

            if ($tipo_movimiento == 2) {
                $idcomprobante = 3;
            } else {
                $idcomprobante = $comprobante;
            }

            $datos = array(
                "id_sesion_caja" => $idSesionCaja,
                "mov_formapago" => 1,
                "id_metodo_pago" => $metodoPago,
                "mov_concepto" => $conceptoCaja,
                "mov_fecha" => date('Y-m-d'),
                "mov_fecha_pago" => date('Y-m-d'),
                "mov_monto" => $monto,
                "mov_estado" => 1,
                "mov_descripcion" => strtoupper($descripcion),
                "mov_hora" => date('H:i:s'),
                "id_tipo_comprobante" => $idcomprobante,
                "tipo_comprobante_descripcion" => $tipo_descripcion,
                "mov_cobro" => "",
                "vaucher" => $nameFile,
                "userRegister" => session()->id,
                "nombreUser" => session()->nombre . ' ' . session()->apellidos,
            );

            $mov->insert($datos);

            if ($mov->db->transStatus() === false) {
                $mov->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $mov->db->transCommit();

            return $this->response->setJSON([
                "status"  => "success",
                "message" => "Se agrego correctamente el movimiento"
            ]);
        } catch (\Exception $e) {
            $mov->db->transRollback();
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrió un error: " . $e->getMessage()
            ]);
        }
    }

    public function showCajero($dateRange)
    {
        if (strpos($dateRange, ' a ') !== false) {
            list($startDate, $endDate) = explode(' a ', $dateRange);

            $startDateFormatted = DateTime::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
            $endDateFormatted = DateTime::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');
        } else {
            $startDateFormatted = DateTime::createFromFormat('d-m-Y', $dateRange)->format('Y-m-d');
            $endDateFormatted = DateTime::createFromFormat('d-m-Y', $dateRange)->format('Y-m-d');
        }

        if (session()->perfil_id == 1 || session()->perfil_id == 2) {
            $sql = "";
        } else {
            $sql = "and m.userRegister = " . session()->id;
        }

        $mov = new MovimientoModel();

        $movimientos = $mov->query("SELECT m.mov_id,m.mov_monto, m.vaucher, m.mov_descripcion, DATE_FORMAT(m.mov_fecha, '%d-%m-%Y') AS fecha, m.mov_fecha, m.mov_concepto, m.id_metodo_pago, mp.metodo, c.caja_descripcion, c2.con_descripcion, m.mov_estado, tm.tipo_movimiento_descripcion, tm.id_tipo_movimiento, u.nombres, u.perfil_id FROM movimiento m 
        inner join sesion_caja sc on sc.id_sesion_caja = m.id_sesion_caja
        inner join sede_caja sc2 on sc2.id_sede_caja = sc.id_sede_caja
        inner join caja c on c.id_caja = sc2.id_caja
        inner join metodos_pagos mp on mp.id = m.id_metodo_pago
        inner join concepto c2 on c2.con_id = m.mov_concepto
        inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
        inner join usuario u on u.id = m.userRegister
        where m.mov_estado != 0 $sql and m.mov_fecha between '$startDateFormatted' and '$endDateFormatted' order by m.mov_id desc")->getResult();

        return $this->response->setJSON($movimientos);
    }

    public function allMetodoPagos()
    {
        $metodo = new MetodoPagoModel();
        $metodos = $metodo->where('estado', 1)->findAll();

        return $this->response->setJSON($metodos);
    }

    public function extornar($id)
    {
        $mov = new MovimientoModel();
        $pagosHono = new PagosHonorariosModel();
        $detalle = new DetallePagosModel();

        try {

            $mov->db->transBegin();

            $verificar = $pagosHono->where('movimientoId', $id)->first();

            if ($verificar) {

                $this->deletePagoArray($verificar['contribuyente_id'], $verificar['monto']);

                $idpago = $verificar['id'];

                $detalle->where('honorario_id', $idpago)->delete();

                $pagosHono->update($idpago, ['estado' => 0]);
            }

            $mov->update($id, ['mov_estado' => 0]);

            if ($mov->db->transStatus() === false) {
                $mov->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $mov->db->transCommit();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se extornó correctamente el movimiento"
            ]);
        } catch (\Exception $e) {
            $mov->db->transRollback();
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrió un error: " . $e->getMessage()
            ]);
        }
    }

    public function editMovimiento()
    {
        $mov = new MovimientoModel();
        $pagosHono = new PagosHonorariosModel();

        try {

            $mov->db->transBegin();

            $idMovimiento = $this->request->getVar('idmov');
            $nuevoMetodoPago = $this->request->getVar('nuevo_metodo_pago');
            $monto = $this->request->getVar('montoEditar');

            $verificar = $pagosHono->where('movimientoId', $idMovimiento)->first();

            if ($verificar) {
                $pagosHono->update($verificar['id'], ['metodo_pago_id' => $nuevoMetodoPago]);
            }

            $mov->update($idMovimiento, ['id_metodo_pago' => $nuevoMetodoPago, 'mov_monto' => $monto]);

            if ($mov->db->transStatus() === false) {
                $mov->db->transRollback();
                throw new \Exception("Error al realizar la operación.");
            }

            $mov->db->transCommit();

            return $this->response->setJSON([
                "status" => "success",
                "message" => "Se editó correctamente"
            ]);
        } catch (\Exception $e) {
            $mov->db->transRollback();
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrió un error: " . $e->getMessage()
            ]);
        }
    }

    public function movimientosGenerales()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        $banco = new BancosModel();

        $bancos = $banco->where('estado', 1)->orderBy('id', 'asc')->findAll();

        return view('movimiento/movimientosGenerales', compact('menu', 'bancos'));
    }

    public function getMovimientosGenerales()
    {
        try {
            $mov = new MovimientoModel();
            $banco = new BancosModel();

            $startDate = $this->request->getVar('desde');
            $endDate = $this->request->getVar('hasta');

            if ($startDate == null || $endDate == null) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Debe seleccionar un rango de fechas"
                ]);
            }

            if ($startDate > $endDate) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "La fecha de inicio debe ser menor a la fecha de fin"
                ]);
            }

            $bancos = $banco->where('estado', 1)->orderBy('id', 'asc')->findAll();

            $datos = $mov->query("SELECT m.mov_id,m.mov_monto, m.mov_descripcion, m.mov_concepto, DATE_FORMAT(m.mov_fecha_pago, '%d-%m-%Y') as mov_fecha_pago, DATE_FORMAT(m.mov_fecha, '%d-%m-%Y') AS fecha, m.mov_fecha, m.id_metodo_pago, mp.id_banco, mp.metodo, c2.con_descripcion, m.mov_estado, tm.tipo_movimiento_descripcion, c2.id_tipo_movimiento FROM movimiento m
            inner join metodos_pagos mp on mp.id = m.id_metodo_pago
            inner join concepto c2 on c2.con_id = m.mov_concepto
            inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
            where m.mov_estado = 1 and m.mov_fecha between '$startDate' and '$endDate' order by m.mov_id desc")->getResult();

            $sumaEfectivoIngresos = $mov->query("SELECT sum(m.mov_monto) as saldo FROM movimiento m
            inner join metodos_pagos mp on mp.id = m.id_metodo_pago
            inner join concepto c2 on c2.con_id = m.mov_concepto
            inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
            where m.mov_estado = 1 and mp.id = 1 and m.mov_fecha < '$startDate' and tm.id_tipo_movimiento = 1")->getRow();

            $sumaEfectivoEgresos = $mov->query("SELECT sum(m.mov_monto) as saldo FROM movimiento m
            inner join metodos_pagos mp on mp.id = m.id_metodo_pago
            inner join concepto c2 on c2.con_id = m.mov_concepto
            inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
            where m.mov_estado = 1 and mp.id = 1 and m.mov_fecha < '$startDate' and tm.id_tipo_movimiento = 2")->getRow();

            $sumaEfectivo = $sumaEfectivoIngresos->saldo - $sumaEfectivoEgresos->saldo + 17.30;

            $data = [];

            $saldoInicialBanks = [];

            foreach ($bancos as $indice => $valor) {

                $idbanco = $valor['id'];

                $sumaIngreso = $mov->query("SELECT sum(m.mov_monto) as saldo FROM movimiento m
                inner join metodos_pagos mp on mp.id = m.id_metodo_pago
                inner join concepto c2 on c2.con_id = m.mov_concepto
                inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
                where m.mov_estado = 1 and m.mov_fecha < '$startDate' and tm.id_tipo_movimiento = 1 and mp.id_banco = $idbanco")->getRow();

                $sumaEgresos = $mov->query("SELECT sum(m.mov_monto) as saldo FROM movimiento m
                inner join metodos_pagos mp on mp.id = m.id_metodo_pago
                inner join concepto c2 on c2.con_id = m.mov_concepto
                inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
                where m.mov_estado = 1 and m.mov_fecha < '$startDate' and tm.id_tipo_movimiento = 2 and mp.id_banco = $idbanco")->getRow();

                $saldoInicialBancos = $sumaIngreso->saldo - $sumaEgresos->saldo + $valor['saldo_inicial'];

                array_push($saldoInicialBanks, round($saldoInicialBancos, 2));
            }

            $fechaSaldoAnterior = new DateTime($startDate);
            $fechaSaldoAnterior->modify('-1 day');
            $fechaSaldoAnterior = $fechaSaldoAnterior->format('d-m-Y');

            $addInicial = [
                "fecha_proceso" => $fechaSaldoAnterior,
                "fecha_pago" => "",
                "tipo" => "SALDO ANTERIOR",
                "concepto" => "",
                "descripcion" => "",
                "metodo" => "",
                "efectivo" => $sumaEfectivo,
                "bancos" => $saldoInicialBanks
            ];

            array_push($data, $addInicial);

            foreach ($datos as $key => $value) {

                $efectivo = 0.00;

                $banks = [];

                if ($value->id_metodo_pago == 1) {

                    if ($value->id_tipo_movimiento == 1) {
                        $efectivo = $value->mov_monto;
                    } else {
                        $efectivo = "-" . $value->mov_monto;
                    }

                    foreach ($bancos as $key2 => $value2) {

                        array_push($banks, 0.00);
                    }
                } else {
                    foreach ($bancos as $key2 => $value2) {

                        if ($value2['id'] == $value->id_banco) {

                            if ($value->id_tipo_movimiento == 1) {
                                array_push($banks, $value->mov_monto);
                            } else {
                                array_push($banks, "-" . $value->mov_monto);
                            }
                        } else {
                            array_push($banks, 0.00);
                        }
                    }
                }

                $add = [
                    "fecha_proceso" => $value->fecha,
                    "fecha_pago" => $value->mov_fecha_pago,
                    "tipo" => $value->tipo_movimiento_descripcion,
                    "concepto" => $value->con_descripcion,
                    "descripcion" => $value->mov_descripcion,
                    "metodo" => $value->metodo,
                    "efectivo" => $efectivo,
                    "bancos" => $banks
                ];

                array_push($data, $add);
            }

            $sumaEfectivoAllIngresos = $mov->query("SELECT sum(m.mov_monto) as saldo FROM movimiento m
            inner join metodos_pagos mp on mp.id = m.id_metodo_pago
            inner join concepto c2 on c2.con_id = m.mov_concepto
            inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
            where m.mov_estado = 1 and mp.id = 1 and m.mov_fecha between '$startDate' and '$endDate' and tm.id_tipo_movimiento = 1")->getRow();

            $sumaEfectivoAllEgresos = $mov->query("SELECT sum(m.mov_monto) as saldo FROM movimiento m
            inner join metodos_pagos mp on mp.id = m.id_metodo_pago
            inner join concepto c2 on c2.con_id = m.mov_concepto
            inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
            where m.mov_estado = 1 and mp.id = 1 and m.mov_fecha between '$startDate' and '$endDate' and tm.id_tipo_movimiento = 2")->getRow();

            $sumaEfectivoAll = $sumaEfectivoAllIngresos->saldo - $sumaEfectivoAllEgresos->saldo + $sumaEfectivo;

            $saldoInicialBanksAll = [];

            foreach ($bancos as $indices => $valors) {

                $idbanco = $valors['id'];

                $sumaIngresoAll = $mov->query("SELECT sum(m.mov_monto) as saldo FROM movimiento m
                inner join metodos_pagos mp on mp.id = m.id_metodo_pago
                inner join concepto c2 on c2.con_id = m.mov_concepto
                inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
                where m.mov_estado = 1 and m.mov_fecha between '$startDate' and '$endDate' and tm.id_tipo_movimiento = 1 and mp.id_banco = $idbanco")->getRow();

                $sumaEgresosAll = $mov->query("SELECT sum(m.mov_monto) as saldo FROM movimiento m
                inner join metodos_pagos mp on mp.id = m.id_metodo_pago
                inner join concepto c2 on c2.con_id = m.mov_concepto
                inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
                where m.mov_estado = 1 and m.mov_fecha between '$startDate' and '$endDate' and tm.id_tipo_movimiento = 2 and mp.id_banco = $idbanco")->getRow();

                $saldoInicialBancosAll = $sumaIngresoAll->saldo - $sumaEgresosAll->saldo + $saldoInicialBanks[$indices];

                array_push($saldoInicialBanksAll, round($saldoInicialBancosAll, 2));
            }

            $addTotal = [
                "fecha_proceso" => "",
                "fecha_pago" => "",
                "tipo" => "TOTALES",
                "concepto" => "",
                "descripcion" => "",
                "metodo" => "",
                "efectivo" => $sumaEfectivoAll,
                "bancos" => $saldoInicialBanksAll
            ];

            array_push($data, $addTotal);

            return $this->response->setJSON($data);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrió un error: " . $e->getMessage()
            ]);
        }
    }

    public function consulta()
    {
        $mov = new MovimientoModel();

        try {
            $idSede = $this->request->getVar('sede');
            $desde = $this->request->getVar('desde');
            $hasta = $this->request->getVar('hasta');

            if ($idSede == null || $desde == null || $hasta == null) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "Debe seleccionar una sede, un rango de fechas y un método de pago"
                ]);
            }

            if ($desde > $hasta) {
                return $this->response->setJSON([
                    "status" => "error",
                    "message" => "La fecha de inicio debe ser menor a la fecha de fin"
                ]);
            }

            $sql = "";

            if ($idSede !== "0") {
                $sql = " AND sc2.id_sede = $idSede";
            }

            $datos = $mov->query("SELECT m.mov_id, DATE_FORMAT(m.mov_fecha, '%d-%m-%Y') as fecha, m.mov_fecha_pago, m.mov_descripcion, m.mov_monto, m.mov_estado, mp.id_banco, mp.metodo, c2.con_descripcion, tm.id_tipo_movimiento, tm.tipo_movimiento_descripcion, se.nombre_sede FROM movimiento m
            inner join sesion_caja sc on sc.id_sesion_caja = m.id_sesion_caja
            inner join sede_caja sc2 on sc2.id_sede_caja = sc.id_sede_caja
            inner join sede se on se.id = sc2.id_sede
            inner join metodos_pagos mp on mp.id = m.id_metodo_pago
            inner join concepto c2 on c2.con_id = m.mov_concepto
            inner join tipo_movimiento tm on tm.id_tipo_movimiento = c2.id_tipo_movimiento
            where m.mov_estado = 1 and m.mov_fecha between '$desde' and '$hasta' $sql ORDER BY m.mov_fecha ASC")->getResult();

            return $this->response->setJSON([
                "status" => "success",
                "data" => $datos
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => "Ocurrió un error: " . $e->getMessage()
            ]);
        }
    }
}
