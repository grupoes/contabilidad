<?php

namespace App\Controllers;

use App\Models\ProfileModel;
use App\Models\PermisosModel;
use App\Models\ModulosModel;


class Permisos extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('permisos/index', compact('menu'));
    }

    public function listProfiles()
    {
        $profile = new ProfileModel();

        if (session()->perfil_id != 1) {
            $perfiles = $profile->where('estado', 1)->where('id !=', 1)->findAll();
        } else {
            $perfiles = $profile->where('estado', 1)->findAll();
        }

        return $this->response->setJSON($perfiles);
    }

    public function show($idperfil)
    {
        $permisos = new PermisosModel();
        $modulos = new ModulosModel();
        $perfil = new ProfileModel();

        $perfil = $perfil->find($idperfil);

        $modulos_padres = $modulos->where('modulo_padre', 0)->where('estado', 1)->orderBy('orden', 'asc')->findAll();

        foreach ($modulos_padres as $key => $value) {
            $hijos = $modulos->where('modulo_padre', $value['id'])->where('estado', 1)->orderBy('orden', 'asc')->findAll();

            foreach ($hijos as $keys => $values) {

                $modulo_id = $values['id'];

                $permiso = $permisos->where('modulo_id', $modulo_id)->where('perfil_id', $idperfil)->first();

                $acciones = $permisos->query("SELECT ma.accion_id, a.nombre_accion FROM modulos_acciones ma INNER JOIN acciones a ON a.id = ma.accion_id WHERE ma.modulo_id = $modulo_id AND ma.accion_id != 1")->getResultArray();

                foreach ($acciones as $keyes => $item) {
                    $peraccion = $permisos->where('modulo_id', $modulo_id)->where('accion_id', $item['accion_id'])->where('perfil_id', $idperfil)->first();

                    if ($peraccion) {
                        $acciones[$keyes]['permiso'] = 1;
                    } else {
                        $acciones[$keyes]['permiso'] = 0;
                    }
                }

                $hijos[$keys]['acciones'] = $acciones;

                if ($permiso) {
                    $hijos[$keys]['permiso'] = 1;
                } else {
                    $hijos[$keys]['permiso'] = 0;
                }
            }

            $modulos_padres[$key]['hijos'] = $hijos;
        }

        return $this->response->setJSON([
            'modulos' => $modulos_padres,
            'perfil' => $perfil['nombre_perfil'],
            'idperfil' => $perfil['id']
        ]);
    }

    public function guardar()
    {
        try {
            if (!session()->logged_in) {
                return redirect()->to(base_url());
            }

            if (!$this->request->getPost('permisos')) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Seleccione al menos un módulo'
                ]);
            }

            $permisos = new PermisosModel();

            $idperfil = $this->request->getPost('perfil_id');
            $modulos = $this->request->getPost('permisos');

            $consulta = $permisos->where('perfil_id', $idperfil)->findAll();

            if ($consulta) {
                $permisos->where('perfil_id', $idperfil)->delete();
            }

            for ($i = 0; $i < count($modulos); $i++) {
                $datos = array(
                    'perfil_id' => $idperfil,
                    'modulo_id' => $modulos[$i],
                    'accion_id' => 1
                );

                $permisos->save($datos);

                if ($this->request->getPost('permisosAcciones-' . $modulos[$i])) {
                    foreach ($this->request->getPost('permisosAcciones-' . $modulos[$i]) as $key => $value) {
                        $datos = array(
                            'perfil_id' => $idperfil,
                            'modulo_id' => $modulos[$i],
                            'accion_id' => $value
                        );

                        $permisos->save($datos);
                    }
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => "Permisos actualizados correctamente"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function savePerfil()
    {
        $profile = new ProfileModel();

        try {
            $data = $this->request->getPost();

            $id = $data['perfil_id'];
            $nombre = $data['nombre_perfil'];

            if ($id == 0) {
                $profile->save([
                    'nombre_perfil' => $nombre,
                    'estado' => 1
                ]);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Perfil creado correctamente'
                ]);
            } else {
                $profile->update($id, [
                    'nombre_perfil' => $nombre,
                    'estado' => 1
                ]);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Perfil actualizado correctamente'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deletePerfil($id)
    {
        $profile = new ProfileModel();

        try {
            $profile->update($id, [
                'estado' => 0
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Perfil eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
