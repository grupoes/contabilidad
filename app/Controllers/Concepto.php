<?php

namespace App\Controllers;

use App\Models\ConceptoModel;
use App\Models\TipoMovimientoModel;

class Concepto extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $tipo = new TipoMovimientoModel();

        $tipos = $tipo->where('tipo_movimiento_estado', 1)->findAll();

        $menu = $this->permisos_menu();

        $permiso_crear = $this->getPermisosAcciones(14, session()->perfil_id, 'crear');

        return view('concepto/index', compact('tipos', 'menu', 'permiso_crear'));
    }

    public function renderConceptos()
    {
        $concepto = new ConceptoModel();

        $conceptos = $concepto->join('tipo_movimiento', 'tipo_movimiento.id_tipo_movimiento = concepto.id_tipo_movimiento')->where('concepto.con_estado', 1)->findAll();

        $permiso_editar = $this->getPermisosAcciones(14, session()->perfil_id, 'editar');
        $permiso_eliminar = $this->getPermisosAcciones(14, session()->perfil_id, 'eliminar');


        foreach ($conceptos as $key => $value) {

            if ($value['con_id'] > 4) {
                $acciones = '';

                if ($permiso_editar) {
                    $acciones .= '<button type="button" class="btn btn-info btn-sm modificar" title="editar" data-id="' . $value['con_id'] . '"><i class="ti ti-edit-circle f-18"></i></button> ';
                }

                if ($permiso_eliminar) {
                    $acciones .= '<button type="button" class="btn btn-danger btn-sm eliminar" title="eliminar" data-id="' . $value['con_id'] . '"><i class="ti ti-trash f-18"></i></button>';
                }

                $conceptos[$key]['acciones'] = $acciones;
            } else {
                $conceptos[$key]['acciones'] = '';
            }
        }

        return $this->response->setJSON($conceptos);
    }

    public function conceptosTipoMovimiento($tipo)
    {
        $concepto = new ConceptoModel();

        $conceptos = $concepto->where('con_estado', 1)->where('id_tipo_movimiento', $tipo)->findAll();
        return $this->response->setJSON($conceptos);
    }

    public function save()
    {
        $concepto = new ConceptoModel();

        try {
            if (!$this->request->is('post')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
            }

            $data = $this->request->getPost();

            $idConcepto = $data['idConcepto'];
            $name = $data['nameConcepto'];
            $tipoMovimiento = $data['tipoMovimiento'];

            $datos = array(
                "id_tipo_movimiento" => $tipoMovimiento,
                "con_descripcion" => $name,
                "con_estado" => 1
            );

            if ($idConcepto == 0) {
                $concepto->insert($datos);

                return $this->response->setJSON(['status' => 'success', 'message' => "Se guardó correctamente."]);
            } else {

                $concepto->update($idConcepto, $datos);

                return $this->response->setJSON(['status' => 'success', 'message' => "Se editó correctamente."]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteConcepto($id)
    {
        $concepto = new ConceptoModel();

        try {
            $data = array("con_estado" => 0);

            $concepto->update($id, $data);

            return $this->response->setJSON(['status' => 'success', 'message' => "Se eliminó correctamente."]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
