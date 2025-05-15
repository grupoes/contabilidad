<?php

namespace App\Controllers;

use App\Models\BancosModel;

class Bancos extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('bancos/index', compact('menu'));
    }

    public function show()
    {
        $banco = new BancosModel();

        $bancos = $banco->where('estado', 1)->findAll();

        return $this->response->setJSON($bancos);
    }

    public function save()
    {
        $banco = new BancosModel();

        try {

            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $nameBanco = $data['nameBanco'];
            $titular = $data['titular'];
            $numeroCuenta = $data['numeroCuenta'];
            $moneda = $data['moneda'];
            $saldoInicial = $data['saldo_inicial'];

            $idBanco = $data['idBanco'];

            $datos = array(
                "nombre_banco" => $nameBanco,
                "moneda" => $moneda,
                "nombre_titular" => $titular,
                "numero_cuenta" => $numeroCuenta,
                "saldo_inicial" => $saldoInicial,
                "estado" => 1
            );

            if ($idBanco == 0) {
                $banco->insert($datos);

                return $this->response->setJSON(['status' => 'success', 'message' => "Se guardó correctamente."]);
            } else {
                $banco->update($idBanco, $datos);

                return $this->response->setJSON(['status' => 'success', 'message' => "Se editó correctamente."]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getBanco($id)
    {
        $banco = new BancosModel();

        $data = $banco->find($id);

        return $this->response->setJSON($data);
    }

    public function delete($id)
    {
        $banco = new BancosModel();

        try {
            $datos = array("estado" => 0);

            $banco->update($id, $datos);

            return $this->response->setJSON(['status' => 'success', 'message' => "Se eliminó correctamente."]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
