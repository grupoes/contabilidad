<?php

namespace App\Controllers;

use App\Models\MetodoPagoModel;
use App\Models\BancosModel;

class MetodoPago extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
			return redirect()->to(base_url());
		}

        $banco = new BancosModel();

        $bancos = $banco->where('estado', 1)->findAll();

        return view('metodoPago/index', compact('bancos'));
    }

    public function show()
    {
        $metodo = new MetodoPagoModel();

        $metodos = $metodo->query("SELECT mp.id, mp.metodo, mp.descripcion, mp.estado, mp.id_banco, b.nombre_banco FROM metodos_pagos as mp LEFT JOIN bancos as b ON b.id = mp.id_banco WHERE mp.estado = 1")->getResult();

        return $this->response->setJSON($metodos);
    }

    public function save()
    {
        $metodo = new MetodoPagoModel();

        try {
            
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $nameMetodo = $data['nameMetodo'];
            $banco = $data['banco'];
            $descripcion = $data['descripcion'];

            $idMetodo = $data['idMetodo'];

            $datos = array(
                "metodo" => $nameMetodo,
                "descripcion" => $descripcion,
                "visible_accion" => 1,
                "estado" => 1,
                "id_banco" => $banco
            );

            if($idMetodo == 0) {
                $metodo->insert($datos);

                return $this->response->setJSON(['status' => 'success', 'message' => "Se guardó correctamente."]);
            } else {
                $metodo->update($idMetodo, $datos);

                return $this->response->setJSON(['status' => 'success', 'message' => "Se editó correctamente."]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getMetodo($id)
    {
        $metodo = new MetodoPagoModel();

        $data = $metodo->find($id);

        return $this->response->setJSON($data);
    }

    public function delete($id)
    {
        $metodo = new MetodoPagoModel();

        try {
            $datos = array("estado" => 0);

            $metodo->update($id, $datos);

            return $this->response->setJSON(['status' => 'success', 'message' => "Se eliminó correctamente."]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }

    }

}
