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
                'typeContri' => $tipo,
            ];

            $mensaje->insert($mensajeData);
            $mensajeId = $mensaje->getInsertID();

            preg_match_all('/{{\s*(\w+)\s*}}/', $message, $matches);

            $mensajeOriginal = $message;

            for ($i = 0; $i < count($destinatarios); $i++) {
                $idContribuyente = $destinatarios[$i];
                $contribuyente = $contri->select('contribuyentes.razon_social, contribuyentes.ruc, numeros_whatsapp.link')->join('numeros_whatsapp', 'numeros_whatsapp.id = contribuyentes.numeroWhatsappId')->find($idContribuyente);
                $consultContacto = $contacto->where('contribuyente_id', $idContribuyente)->where('estado', 1)->findAll();

                $messagePersonalizado = $mensajeOriginal;

                if ($consultContacto) {
                    foreach ($consultContacto as $key => $value) {

                        foreach ($matches[0] as $index => $placeholder) {
                            $varName = $matches[1][$index];
                            $replacement = '';

                            switch ($varName) {
                                case 'RAZON_SOCIAL':
                                    $replacement = $contribuyente['razon_social'];
                                    break;
                                case 'RUC':
                                    $replacement = $contribuyente['ruc'];
                                    break;
                                case 'NOMBRE_CONTACTO':
                                    $replacement = $value['nombre_contacto'];
                                    break;
                            }

                            $messagePersonalizado = str_replace($placeholder, $replacement, $messagePersonalizado);
                        }

                        $envioData = [
                            'mensaje_id' => $mensajeId,
                            'contacto_id' => $value['id'],
                            'message' => $messagePersonalizado,
                            'numero_whatsapp' => $value['numero_whatsapp'],
                            'nombre_contacto' => $value['nombre_contacto'],
                            'razon_social' => $contribuyente['razon_social'],
                            'ruc' => $contribuyente['ruc'],
                            'link' => $contribuyente['link'],
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

    public function listaMensajes()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $permiso_crear = $this->getPermisosAcciones(36, session()->perfil_id, 'crear');

        $menu = $this->permisos_menu();

        return view('mensajes/lista', compact('menu', 'permiso_crear'));
    }

    public function mensajesAll()
    {
        $mensaje = new MensajeModel();

        $consulta = $mensaje->select("id, titulo, contenido, DATE_FORMAT(fechaCreacion, '%d-%m-%Y %H:%i:%s') as fecha, creadoPor, typeContri")->where('estado', 1)->orderBy('id', 'desc')->findAll();

        $permiso_ver_detalle = $this->getPermisosAcciones(36, session()->perfil_id, 'ver detalle');
        $permiso_eliminar = $this->getPermisosAcciones(36, session()->perfil_id, 'eliminar');

        foreach ($consulta as $key => $value) {
            $acciones = '<ul class="list-inline me-auto mb-0">';

            $id = $value['id'];
            $titulo = $value['titulo'];

            if ($permiso_ver_detalle) {
                $acciones .= '<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Ver Mensajes">
                            <a href="#" onclick="verMensajes(event, ' . $id . ', ' . $titulo . ')" class="avtar avtar-xs btn-link-success btn-pc-default"><i class="ti ti-eye f-18"></i></a>
                        </li>';
            }

            if ($permiso_eliminar) {
                $acciones .= '<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Eliminar">
                            <a href="#" onclick="eliminarMensaje(event, ' . $id . ')" class="avtar avtar-xs btn-link-danger btn-pc-default"><i class="ti ti-trash f-18"></i></a>
                        </li>';
            }

            $acciones .= '</ul>';

            $consulta[$key]['acciones'] = $acciones;
        }

        return $this->response->setJSON($consulta);
    }

    public function mensajesAllId($id)
    {
        $envio = new EnviosModel();

        $consulta = $envio->select("id, message, DATE_FORMAT(fecha_envio, '%d-%m-%Y %H:%i:%s') as fecha_envio, numero_whatsapp, razon_social, estado")->where('mensaje_id', $id)->orderBy("FIELD(estado, 'no enviado', 'pendiente', 'enviado')", '', false)->findAll();

        return $this->response->setJSON($consulta);
    }

    public function delete($id)
    {
        $mensaje = new MensajeModel();

        try {
            $id_user = session()->id;
            $fecha_eliminacion = date('Y-m-d H:i:s');

            $datos = [
                'delete_id_user' => $id_user,
                'fecha_eliminacion' => $fecha_eliminacion,
                'estado' => 0,
            ];

            $mensaje->update($id, $datos);

            return $this->response->setJSON(['status' => 'success', 'message' => 'Mensaje eliminado correctamente']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
