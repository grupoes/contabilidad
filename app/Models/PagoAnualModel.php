<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoAnualModel extends Model
{
    protected $table      = 'pago_anual';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'pdt_anual_id', 'contribuyente_id', 'fecha_pago', 'fecha_proceso', 'monto_total', 'anio_correspondiente', 'monto_pagado', 'monto_pendiente', 'usuario_id_cobra', 'estado'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
