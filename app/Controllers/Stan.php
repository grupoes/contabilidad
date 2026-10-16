<?php

namespace App\Controllers;

use App\Models\LibroModel;
use App\Models\StanModel;

/**
 * Mantenimiento de stan por sede (Configuración > Stan).
 * stan_estado: 1 = activo, 2 = inactivo, 0 = eliminado (baja lógica).
 */
class Stan extends BaseController
{
    private function moduloId()
    {
        $modulo = db_connect()->table('modulos')->select('id')->where('url', 'configuracion/stan')->get()->getRowArray();

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
        $sedes = (new LibroModel())->sedes();

        return view('configuracion/stan', compact('menu', 'isCrear', 'sedes'));
    }

    public function renderStans()
    {
        if (!$this->permiso('listar')) {
            return $this->response->setJSON([]);
        }

        $stans = (new StanModel())
            ->select('stan.*, COALESCE(sede.nombre_sede, stan.stan_sede) AS sede_nombre')
            ->join('sede', 'sede.id = stan.sede_id', 'left')
            ->where('stan.stan_estado !=', 0)->orderBy('sede_nombre', 'ASC')->orderBy('stan.stan_numero', 'ASC')->findAll();

        $editar = $this->permiso('editar');
        $eliminar = $this->permiso('eliminar');

        foreach ($stans as $k => $s) {
            $acciones = '';

            if ($editar) {
                $acciones .= '<button type="button" class="btn btn-info btn-sm editar" title="editar" data-id="' . $s['stan_id'] . '"><i class="ti ti-edit-circle f-18"></i></button> ';
            }

            if ($eliminar) {
                $acciones .= '<button type="button" class="btn btn-danger btn-sm eliminar" title="eliminar" data-id="' . $s['stan_id'] . '"><i class="ti ti-trash f-18"></i></button>';
            }

            $stans[$k]['acciones'] = $acciones;
        }

        return $this->response->setJSON($stans);
    }

    public function getStan($id)
    {
        if (!$this->permiso('editar')) {
            return $this->sinPermiso();
        }

        $stan = (new StanModel())->where('stan_estado !=', 0)->find($id);

        if (!$stan) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Stan no encontrado']);
        }

        return $this->response->setJSON(['status' => 'success', 'stan' => $stan]);
    }

    public function saveStan()
    {
        try {
            $d = $this->request->getPost();
            $id = (int) ($d['idStan'] ?? 0);

            if (!$this->permiso($id == 0 ? 'crear' : 'editar')) {
                return $this->sinPermiso();
            }

            $sedeId = (int) ($d['sede'] ?? 0);
            $numero = trim((string) ($d['numero'] ?? ''));
            $estado = (int) ($d['estado'] ?? 1);

            if ($sedeId === 0 || $numero === '') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'La sede y el N° de stan son obligatorios.']);
            }

            $sede = (new LibroModel())->sedeActiva($sedeId);
            if (!$sede) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sede no válida.']);
            }

            if (!in_array($estado, [1, 2], true)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Estado no válido.']);
            }

            $model = new StanModel();

            // El N° de stan no se repite dentro de la misma sede (entre los no eliminados)
            $repetido = $model->where('sede_id', $sede['id'])->where('stan_numero', $numero)->where('stan_estado !=', 0);
            if ($id > 0) {
                $repetido->where('stan_id !=', $id);
            }

            if ($repetido->first()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Ya existe ese N° de stan en la sede ' . $sede['nombre_sede'] . '.']);
            }

            $datos = [
                'stan_sede' => $sede['nombre_sede'],
                'sede_id' => (int) $sede['id'],
                'stan_numero' => $numero,
                'stan_estado' => $estado,
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

    public function deleteStan($id)
    {
        try {
            if (!$this->permiso('eliminar')) {
                return $this->sinPermiso();
            }

            (new StanModel())->update($id, ['stan_estado' => 0]);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Se eliminó correctamente.']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
