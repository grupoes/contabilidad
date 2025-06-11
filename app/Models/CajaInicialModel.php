<?php

namespace App\Models;

use CodeIgniter\Model;

class CajaInicialModel extends Model
{
    protected $table      = 'caja_inicial';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'metodo_id', 'sede_id', 'monto_inicial'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
