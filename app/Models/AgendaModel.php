<?php

namespace App\Models;

use CodeIgniter\Model;

class AgendaModel extends Model
{
    protected $table      = 'agenda';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'title', 'description', 'start', 'estado', 'dias_notificar', 'horas_notificar', 'fecha_notificar', 'allDay', 'evidencia', 'file_evidencia', 'user_add', 'user_asignado', 'user_edit', 'user_delete', 'deleted_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
