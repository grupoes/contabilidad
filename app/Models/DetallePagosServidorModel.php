<?php

namespace App\Models;

use CodeIgniter\Model;

class DetallePagosServidorModel extends Model
{
    protected $table      = 'servidor_amortizacion';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'pago_servidor_id', 'pago_amortizacion_id', 'monto'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
