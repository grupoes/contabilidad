<?php

namespace App\Controllers;

use App\Models\MensajeModel;
use App\Models\EnviosModel;
use App\Models\ContactosContribuyenteModel;
use App\Models\ContribuyenteModel;

class Mensajes extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('mensajes/masivo', compact('menu'));
    }

    public function guardarMensajeMasivo()
    {
        $mensaje = new MensajeModel();
        $envio = new EnviosModel();
        $contacto = new ContactosContribuyenteModel();
        $contri = new ContribuyenteModel();

        try {
            $message = $this->request->getPost('message');
            $tipo = $this->request->getPost('contribuyenteType');
            $titulo = $this->request->getPost('titulo');
            $destinatarios = $this->request->getPost('contribuyentes');

            $fechaCreacion = date('Y-m-d H:i:s');

            $mensajeData = [
                'titulo' => $titulo,
                'contenido' => $message,
                'fechaCreacion' => $fechaCreacion,
                'creadoPor' => session()->id,
            ];

            $mensaje->insert($mensajeData);
            $mensajeId = $mensaje->getInsertID();

            for ($i = 0; $i < count($destinatarios); $i++) {
                $idContribuyente = $destinatarios[$i];
                $contribuyente = $contri->select('razon_social')->find($idContribuyente);
                $consultContacto = $contacto->where('contribuyente_id', $idContribuyente)->findAll();

                if ($consultContacto) {
                    foreach ($consultContacto as $key => $value) {
                        $envioData = [
                            'mensaje_id' => $mensajeId,
                            'contacto_id' => $value['id'],
                            'numero_whatsapp' => $value['numero_whatsapp'],
                            'nombre_contacto' => $value['nombre_contacto'],
                            'razon_social' => $contribuyente['razon_social'],
                        ];

                        $envio->insert($envioData);
                    }
                }
            }

            return $this->response->setJSON([
                'status' => "success",
                'message' => "Mensaje guardado correctamente",
                'messageId' => $mensajeId
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => "error",
                'message' => $e->getMessage(),
            ]);
        }
    }
}
