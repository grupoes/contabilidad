<?php

namespace App\Controllers;

use App\Models\FeriadoModel;
use CodeIgniter\RESTful\ResourceController;

class Feriados extends ResourceController
{
    protected $modelName = 'App\Models\FeriadoModel';
    protected $format    = 'json';

    // GET /feriados
    public function index()
    {
        return $this->respond($this->model->findAll());
    }

    // GET /feriados/fecha/2025-07-28
    public function porFecha($fecha)
    {
        $data = $this->model->where('fecha', $fecha)->findAll();
        return $this->respond($data);
    }

    // GET /feriados/rango?inicio=2025-01-01&fin=2025-12-31
    public function porRango()
    {
        $inicio = $this->request->getGet('inicio');
        $fin    = $this->request->getGet('fin');

        if (!$inicio || !$fin) {
            return $this->fail('Debe enviar inicio y fin');
        }

        return $this->respond(
            $this->model
                ->where('fecha >=', $inicio)
                ->where('fecha <=', $fin)
                ->findAll()
        );
    }

    // GET /feriados/mes/2025/07
    public function porMes($anio, $mes)
    {
        $data = $this->model
            ->like('fecha', "$anio-$mes", 'after')
            ->findAll();

        return $this->respond($data);
    }

    // GET /feriados/es-feriado/2025-01-01
    public function esFeriado($fecha)
    {
        $feriado = $this->model->where('fecha', $fecha)->first();

        if ($feriado) {
            return $this->respond([
                "fecha" => $fecha,
                "esFeriado" => true,
                "descripcion" => $feriado['descripcion']
            ]);
        }

        return $this->respond([
            "fecha" => $fecha,
            "esFeriado" => false
        ]);
    }
}
