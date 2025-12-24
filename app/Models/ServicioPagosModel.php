<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioPagosModel extends Model
{
    protected $table      = 'servicio_pagos';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'servicio_id', 'fecha_pago', 'fecha_proceso', 'monto', 'fecha_programacion', 'monto_pagado', 'monto_pendiente', 'user_add', 'user_edit', 'user_delete', 'estado', 'created_at', 'updated_at', 'deleted_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
