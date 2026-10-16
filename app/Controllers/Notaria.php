<?php

namespace App\Controllers;

use App\Models\NotariaModel;

/**
 * Mantenimiento de notarías (Configuración > Notarias).
 * not_estado: 1 = activo, 2 = inactivo, 0 = eliminado (baja lógica).
 */
class Notaria extends BaseController
{
    private function moduloId()
    {
        $modulo = db_connect()->table('modulos')->select('id')->where('url', 'configuracion/notarias')->get()->getRowArray();

        return $modulo ? $modulo['id'] : 0;
    }

    private function permiso($accion)
    {
        return $this->getPermisosAcciones($this->moduloId(), session()->perfil_id, $accion);
    }

    private function sinPermiso()
    {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Sin permisos']);
    }

    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        if (!$this->permiso('listar')) {
            return redirect()->to(base_url('home'));
        }

        $menu = $this->permisos_menu();
        $isCrear = (bool) $this->permiso('crear');

        return view('configuracion/notarias', compact('menu', 'isCrear'));
    }

    public function renderNotarias()
    {
        if (!$this->permiso('listar')) {
            return $this->response->setJSON([]);
        }

        $notarias = (new NotariaModel())->where('not_estado !=', 0)->orderBy('not_razon_social', 'ASC')->findAll();

        $editar = $this->permiso('editar');
        $eliminar = $this->permiso('eliminar');

        foreach ($notarias as $k => $n) {
            $acciones = '';

            if ($editar) {
                $acciones .= '<button type="button" class="btn btn-info btn-sm editar" title="editar" data-id="' . $n['not_id'] . '"><i class="ti ti-edit-circle f-18"></i></button> ';
            }

            if ($eliminar) {
                $acciones .= '<button type="button" class="btn btn-danger btn-sm eliminar" title="eliminar" data-id="' . $n['not_id'] . '"><i class="ti ti-trash f-18"></i></button>';
            }

            $notarias[$k]['acciones'] = $acciones;
        }

        return $this->response->setJSON($notarias);
    }

    public function getNotaria($id)
    {
        if (!$this->permiso('editar')) {
            return $this->sinPermiso();
        }

        $notaria = (new NotariaModel())->where('not_estado !=', 0)->find($id);

        if (!$notaria) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Notaría no encontrada']);
        }

        return $this->response->setJSON(['status' => 'success', 'notaria' => $notaria]);
    }

    public function saveNotaria()
    {
        try {
            $d = $this->request->getPost();
            $id = (int) ($d['idNotaria'] ?? 0);

            if (!$this->permiso($id == 0 ? 'crear' : 'editar')) {
                return $this->sinPermiso();
            }

            $ruc = trim((string) ($d['ruc'] ?? ''));
            $razon = trim((string) ($d['razonSocial'] ?? ''));
            $estado = (int) ($d['estado'] ?? 1);

            if ($ruc === '' || $razon === '') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'El RUC y la razón social son obligatorios.']);
            }

            if (!preg_match('/^\d{11}$/', $ruc)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'El RUC debe tener 11 dígitos numéricos.']);
            }

            if (!in_array($estado, [1, 2], true)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Estado no válido.']);
            }

            $model = new NotariaModel();

            // RUC único entre las notarías no eliminadas
            $repetida = $model->where('not_ruc', $ruc)->where('not_estado !=', 0);
            if ($id > 0) {
                $repetida->where('not_id !=', $id);
            }

            if ($repetida->first()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Ya existe una notaría con ese RUC.']);
            }

            $datos = [
                'not_ruc' => $ruc,
                'not_razon_social' => $razon,
                'not_estado' => $estado,
            ];

            if ($id == 0) {
                $model->insert($datos);

                return $this->response->setJSON(['status' => 'success', 'message' => 'Se guardó correctamente.']);
            }

            $model->update($id, $datos);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Se editó correctamente.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteNotaria($id)
    {
        try {
            if (!$this->permiso('eliminar')) {
                return $this->sinPermiso();
            }

            (new NotariaModel())->update($id, ['not_estado' => 0]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Se eliminó correctamente.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
