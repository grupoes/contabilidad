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

            $start = $this->request->getPost('date') . " " . $this->request->getPost('time');
            $opcion = $this->request->getPost('opcion');
            $notify_time = $this->request->getPost('notify_time');
            $id = $this->request->getPost('agenda_id');

            if ($opcion == 1) {
                $dias_notificar = $notify_time;
                $horas_notificar = "00:00";
            } else {
                $dias_notificar = 0;
                $horas_notificar = $notify_time;

                if (!$this->validarHora($horas_notificar)) {
                    return $this->response->setJSON([
                        'status'  => 'error',
                        'message' => 'La hora debe estar en formato HH:MM (00:00 a 23:59)'
                    ]);
                }
            }

            $data = [
                'title' => $this->request->getPost('title'),
                'start' => $start,
                'description' => $this->request->getPost('description'),
                'allDay' => 0,
                'dias_notificar' => $dias_notificar,
                'horas_notificar' => $horas_notificar,
                'estado' => 'pendiente',
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
}
