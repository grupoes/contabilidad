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

        $menu = $this->permisos_menu();

        $crear = $this->getPermisosAcciones(17, session()->perfil_id, 'crear');

        return view('metodoPago/index', compact('bancos', 'menu', 'crear'));
    }

    public function show()
    {
        $metodo = new MetodoPagoModel();

        $editar = $this->getPermisosAcciones(17, session()->perfil_id, 'editar');
        $eliminar = $this->getPermisosAcciones(17, session()->perfil_id, 'eliminar');

        $metodos = $metodo->query("SELECT mp.id, mp.metodo, mp.descripcion, mp.estado, mp.id_banco, b.nombre_banco FROM metodos_pagos as mp LEFT JOIN bancos as b ON b.id = mp.id_banco WHERE mp.estado = 1")->getResult();

        foreach ($metodos as $key => $value) {
            $acciones = "";

            if ($value->id != 1) {
                $acciones .= '<ul class="list-inline me-auto mb-0">';

                if ($editar) {
                    $acciones .= '
                    <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Editar">
                        <a href="#" onclick="editarMetodo(event, ' . $value->id . ')" class="avtar avtar-xs btn-link-success btn-pc-default"><i class="ti ti-edit-circle f-18"></i></a>
                    </li>';
                }

                if ($eliminar) {
                    $acciones .= '
                    <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Eliminar">
                        <a href="#" onclick="deleteMetodo(event, ' . $value->id . ')" class="avtar avtar-xs btn-link-danger btn-pc-default"><i class="ti ti-trash f-18"></i></a>
                    </li>';
                }

                $acciones .= '</ul>';
            }

            $metodos[$key]->acciones = $acciones;
        }

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

            if ($idMetodo == 0) {
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
