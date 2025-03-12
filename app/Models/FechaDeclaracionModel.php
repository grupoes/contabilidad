<?php

namespace App\Models;

use CodeIgniter\Model;

class FechaDeclaracionModel extends Model
{
    protected $table      = 'fecha_declaracion';
    protected $primaryKey = 'id_fecha_declaracion';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id_fecha_declaracion', 'id_anio', 'id_mes', 'id_numero', 'fecha_exacta', 'fecha_declaracion_estado', 'id_tributo', 'dia_exacto', 'fecha_notificar'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
