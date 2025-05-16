<?php

namespace App\Models;

use CodeIgniter\Model;

class HistorialTarifaModel extends Model
{
    protected $table      = 'historial_tarifas';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'contratoId', 'fecha_inicio', 'fecha_fin', 'monto_mensual', 'monto_anual', 'estado'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
