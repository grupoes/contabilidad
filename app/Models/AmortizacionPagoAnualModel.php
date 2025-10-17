<?php

namespace App\Models;

use CodeIgniter\Model;

class AmortizacionPagoAnualModel extends Model
{
    protected $table      = 'amortizacion_pago_anual';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'pago_anual_id', 'amop_id', 'monto'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
