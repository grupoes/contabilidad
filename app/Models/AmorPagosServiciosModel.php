<?php

namespace App\Models;

use CodeIgniter\Model;

class AmorPagosServiciosModel extends Model
{
    protected $table      = 'amor_pagos_servicios';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'servicio_id', 'movimientoId', 'registro', 'fecha_pago', 'metodo_pago_id', 'monto', 'vaucher', 'estado', 'user_add', 'user_edit', 'user_delete', 'created_at', 'updated_at', 'deleted_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
