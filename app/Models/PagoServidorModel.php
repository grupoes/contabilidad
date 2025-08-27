<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoServidorModel extends Model
{
    protected $table      = 'pago_servidor';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'cotribuyente_id', 'fecha_pago', 'monto_total', 'anio_correspondiente', 'montoPagado', 'montoPendiente', 'montoExcedente', 'usuario_id_cobra', 'estado', 'fecha_proceso'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
