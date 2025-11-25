<?php

namespace App\Controllers;

use App\Models\DeclaracionModel;
use App\Models\PdtModel;
use App\Models\AnioModel;
use App\Models\MesModel;
use App\Models\NumeroModel;
use App\Models\TributoModel;
use App\Models\FechaDeclaracionModel;
use App\Models\FechaDeclaracionBalanceModel;

class Declaracion extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $declaracion = new DeclaracionModel();

        $declaraciones = $declaracion->where('id_declaracion', 1)->orWhere('id_declaracion', 4)->findAll();

        $menu = $this->permisos_menu();

        return view('declaracion/index', compact('declaraciones', 'menu'));
    }

    public function listaDeclaracion($id)
    {
        $pdt = new PdtModel();
        $lista = $pdt->where('id_declaracion', $id)->findAll();

        return $this->response->setJSON($lista);
    }

    public function calendario()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        if (!$this->request->getPost('lista')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Seleccione un item',
            ]);
        }

        $lista = $this->request->getPost('lista');
        $iddeclaracion = $this->request->getPost('id_declaracion');

        $anio = new AnioModel();
        $mes = new MesModel();
        $numero = new NumeroModel();
        $pdt = new PdtModel();
        $declaracion = new DeclaracionModel();

        $dataPdt = $pdt->find($lista);
        $dataDecl = $declaracion->find($iddeclaracion);

        $anios = $anio->where('anio_descripcion <=', date('Y'))->orderBy('anio_descripcion', 'desc')->findAll();
        $meses = $mes->findAll();
        $numeros = $numero->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'anios' => $anios,
            'meses' => $meses,
            'numeros' => $numeros,
            'lista' => $lista,
            'iddeclaracion' => $iddeclaracion,
            'pdtNombre' => $dataPdt['pdt_descripcion'],
            'declaracion' => $dataDecl['decl_nombre']
        ]);
    }

    public function extraer_data()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $anio = $this->request->getPost('anio');
        $lista = $this->request->getPost('lista');

        $tributo = new TributoModel();
        $fecha = new FechaDeclaracionModel();

        $sql = $tributo->where('id_pdt', $lista)->first();

        $datos = $fecha->select('CONCAT(id_numero, id_mes) as numeracion, CONCAT(id_numero, 1) as numeracionBalance, dia_exacto, fecha_exacta')
            ->where('id_tributo', $sql['id_tributo'])
            ->where('id_anio', $anio)
            ->findAll();

        return $this->response->setJSON($datos);
    }

    public function guardar_datos()
    {
        try {
            if (!session()->logged_in) {
                return redirect()->to(base_url());
            }

            $tributo = new TributoModel();
            $anio_ = new AnioModel();
            $fecha = new FechaDeclaracionModel();
            $month = new MesModel();

            $ani = $this->request->getPost('id_anio');
            $mes = $this->request->getPost('id_mes');
            $numero = $this->request->getPost('id_numero');
            $dia = $this->request->getPost('dia');
            $lista = $this->request->getPost('lista');

            $pdt = $tributo->where('id_pdt', $lista)->findAll();

            foreach ($pdt as $key => $value) {
                $dataAnio = $anio_->find($ani);
                $anio = $dataAnio['anio_descripcion'];

                $id_existe = "";

                if ($lista == 3) {
                    $mes = date('m', strtotime($dia));
                }

                $tribut = $fecha->where('id_anio', $ani)
                    ->where('id_mes', $mes)
                    ->where('id_numero', $numero)
                    ->where('id_tributo', $value['id_tributo'])
                    ->findAll();

                foreach ($tribut as $clave => $tributos) {
                    $id_existe = $tributos['id_fecha_declaracion'];
                }

                $mes_exact = $month->find($mes);

                $mes_exacto = $mes_exact['mes_id_mes'];
                if ($mes_exacto == "1") {
                    $anio = (int) $anio + 1;
                }

                if ($dia === "") {
                    $fecha_notificacion = "";
                    $fecha_final = "";
                    $dia_exacto = $dia;
                } else {

                    if ($lista == 1 || $lista == 16) {
                        $fecha_final = $anio . "-" . $mes_exacto . "-" . $dia;

                        $date = date_create($fecha_final);
                        date_add($date, date_interval_create_from_date_string('-3 days'));
                        $fecha_notificacion = date_format($date, "Y-m-d");

                        $dia_exacto = $dia;
                    } else {
                        $fecha_final = $dia;

                        $date = date_create($fecha_final);
                        date_add($date, date_interval_create_from_date_string('-20 days'));
                        $fecha_notificacion = date_format($date, "Y-m-d");

                        $dia_exacto = date('d', strtotime($fecha_final));
                    }
                }

                $data = array(
                    "id_anio" => $ani,
                    "id_mes" => $mes,
                    "id_numero" => $numero,
                    "id_tributo" => $value['id_tributo'],
                    "fecha_exacta" => $fecha_final,
                    "fecha_notificar" => $fecha_notificacion,
                    "dia_exacto" => $dia_exacto
                );

                if ($id_existe == "") {

                    $fecha->insert($data);
                } else {
                    $fecha->update($id_existe, $data);
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Datos guardados correctamente',
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ocurrio un error al guardar los datos ' . $e->getMessage(),
            ]);
        }
    }
}
