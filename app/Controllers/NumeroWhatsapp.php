<?php

namespace App\Controllers;

use App\Models\NumeroWhatsappModel;

class NumeroWhatsapp extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('configuracion/numeroWhatsapp', compact('menu'));
    }

    public function allNumeroWhatsapp()
    {
        $model = new NumeroWhatsappModel();

        $data = $model->where("estado", 1)->findAll();

        return $this->response->setJSON($data);
    }

    public function saveNumeroWhatsapp()
    {
        $model = new NumeroWhatsappModel();
        try {
            $data = $this->request->getPost();

            $datos = array(
                "titulo" => $data["nombre_whatsapp"],
                "numero" => $data["numero_whatsapp"],
                "link" => $data["link"],
                "estado" => 1
            );

            if ($data['idNumero'] == 0) {
                $dat = $model->insert($datos);

                if ($dat) {
                    $status = "success";
                    $msg = "Guardado con éxito";
                } else {
                    $status = "error";
                    $msg = "No se pudo guardar";
                }
            } else {
                $dat = $model->update($data['idNumero'], $datos);

                if ($dat) {
                    $status = "success";
                    $msg = "Actualizado con éxito";
                } else {
                    $status = "error";
                    $msg = "No se pudo actualizar";
                }
            }

            return $this->response->setJSON([
                "status" => $status,
                "message" => $msg
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    public function getIdNumeroWhatsapp($id)
    {
        $model = new NumeroWhatsappModel();

        $data = $model->find($id);

        return $this->response->setJSON($data);
    }

    public function deleteWhatsapp($id)
    {
        $model = new NumeroWhatsappModel();

        $data = $model->update($id, ["estado" => 0]);

        if ($data) {
            $status = "success";
            $msg = "Eliminado con éxito";
        } else {
            $status = "error";
            $msg = "No se pudo eliminar";
        }

        return $this->response->setJSON([
            "status" => $status,
            "message" => $msg
        ]);
    }
}
