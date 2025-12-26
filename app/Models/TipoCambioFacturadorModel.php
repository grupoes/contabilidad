<?php

namespace App\Models;

use CodeIgniter\Model;

class TipoCambioFacturadorModel extends Model
{
    protected $DBGroup = 'facturador';

    protected $table      = 'tipo_cambio';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'compra', 'venta', 'origen', 'moneda', 'fecha'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
