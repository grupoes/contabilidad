<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoAmoAnualModel extends Model
{
    protected $table      = 'amo_pagos_anual';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'contribuyente_id', 'movimientoId', 'registro', 'fecha', 'fecha_pago', 'metodo_pago_id', 'monto', 'vaucher', 'estado'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
