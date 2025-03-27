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

        $profile = new ProfileModel();

        if (session()->perfil_id != 1) {
            $perfiles = $profile->where('estado', 1)->where('id !=', 1)->findAll();
        } else {
            $perfiles = $profile->where('estado', 1)->findAll();
        }

        $menu = $this->permisos_menu();

        return view('permisos/index', compact('perfiles', 'menu'));
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
                $permiso = $permisos->where('modulo_id', $values['id'])->where('perfil_id', $idperfil)->first();

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
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "Permisos actualizados correctamente"
        ]);
    }
}
