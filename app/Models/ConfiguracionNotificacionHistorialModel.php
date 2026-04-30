<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfiguracionNotificacionHistorialModel extends Model
{
    protected $table      = 'configuracion_notificacion_historial';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['contribuyente_id', 'tributo_id', 'nombre_tributo', 'fecha_cambio', 'usuario_id', 'mes', 'anio', 'estado'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
