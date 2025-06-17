<?php

namespace App\Models;

use CodeIgniter\Model;

class DetallePagosModel extends Model
{
    protected $table      = 'pagos_amortizaciones';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'pago_id', 'honorario_id', 'monto'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
