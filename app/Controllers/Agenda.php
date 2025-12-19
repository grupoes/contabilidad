<?php

namespace App\Controllers;

use App\Models\AgendaModel;

class Agenda extends BaseController
{
    public function index()
    {
        if (!session()->logged_in) {
            return redirect()->to(base_url());
        }

        $menu = $this->permisos_menu();

        return view('agenda/index', compact('menu'));
    }

    public function getAgenda()
    {
        $agenda = new AgendaModel();
        $agendaAll = $agenda->select("id, title, DATE_FORMAT(start, '%Y-%m-%dT%H:%i:%s') AS start, description, allDay, dias_notificar, horas_notificar")->findAll();

        foreach ($agendaAll as $key => $value) {

            $agendaAll[$key]['allDay'] = (bool) $value['allDay'];
        }
        return $this->response->setJSON($agendaAll);
    }

    public function validarHora($hora)
    {
        return preg_match('/^(2[0-3]|[01]\d):([0-5]\d)$/', $hora);
    }

    public function save()
    {
        $agenda = new AgendaModel();

        try {

            $date = $this->request->getPost('date');
            $time = $this->request->getPost('time');
            $start = $date . " " . $time;
            $opcion = $this->request->getPost('opcion');
            $notify_time = $this->request->getPost('notify_time');
            $id = $this->request->getPost('agenda_id');

            if ($opcion == 1) {
                $dias_notificar = $notify_time;
                $horas_notificar = "00:00";

                $fecha = new \DateTime($date);

                // Restar 5 días
                $fecha->sub(new \DateInterval('P' . $notify_time . 'D'));

                $fecha_notificar = $fecha->format('Y-m-d') . " " . $time;
            } else {
                $dias_notificar = 0;
                $horas_notificar = $notify_time;

                if (!$this->validarHora($horas_notificar)) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => 'La hora debe estar en formato HH:MM (00:00 a 23:59)'
                    ]);
                }

                list($horas, $minutos) = explode(':', $notify_time);

                // Crear intervalo dinámico
                $intervalo = 'PT';

                if ((int)$horas > 0) {
                    $intervalo .= (int)$horas . 'H';
                }

                if ((int)$minutos > 0) {
                    $intervalo .= (int)$minutos . 'M';
                }

                $fecha = new \DateTime($start);
                $fecha->sub(new \DateInterval($intervalo));

                $fecha_notificar = $fecha->format('Y-m-d H:i:s');
            }

            $data = [
                'title' => $this->request->getPost('title'),
                'start' => $start,
                'description' => $this->request->getPost('description'),
                'allDay' => 0,
                'dias_notificar' => $dias_notificar,
                'horas_notificar' => $horas_notificar,
                'fecha_notificar' => $fecha_notificar,
                'estado' => 'pendiente',
                'user_asignado' => session()->id,
                'user_add' => session()->id,
            ];

            if ($id == 0) {
                $agenda->insert($data);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Actividad agregada correctamente']);
            } else {
                $agenda->update($id, $data);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Actividad actualizada correctamente']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => "Actividad no agregada. " . $e->getMessage()]);
        }
    }

    public function actividadesHoy()
    {
        $agenda = new AgendaModel();
        $agendaAll = $agenda->select("id, title, description, estado, DATE_FORMAT(start, '%h:%i %p') AS hora")->where('DATE(fecha_notificar) <= CURDATE()')->where('estado', 'pendiente')->where('user_asignado', session()->id)->findAll();

        return $this->response->setJSON($agendaAll);
    }

    public function atendidoActividadSinEvidencia($id)
    {
        $agenda = new AgendaModel();

        try {
            $data = [
                'estado' => 'atendido_sin_evidencia',
            ];

            $agenda->update($id, $data);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Actividad marcada como atendida sin evidencia']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => "No se pudo actualizar la actividad. " . $e->getMessage()]);
        }
    }

    public function atendidoActividadConEvidencia()
    {
        $agenda = new AgendaModel();

        try {
            $id = $this->request->getPost('actividad_id');
            $evidencia = $this->request->getFile('evidencia');

            if ($evidencia && $evidencia->isValid() && !$evidencia->hasMoved()) {
                $nuevoNombre = $evidencia->getRandomName();
                $evidencia->move(FCPATH . 'evidencias/', $nuevoNombre);

                $data = [
                    'evidencia' => "SI",
                    'file_evidencia' => $nuevoNombre,
                    'estado' => 'atendido_con_evidencia',
                ];

                $agenda->update($id, $data);

                return $this->response->setJSON(['status' => 'success', 'message' => 'Actividad marcada como atendida con evidencia']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo subir el archivo de evidencia.']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => "No se pudo actualizar la actividad. " . $e->getMessage()]);
        }
    }
}
