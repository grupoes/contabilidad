<?php

namespace App\Models;

use CodeIgniter\Model;

class ServidorModel extends Model
{
    protected $table      = 'servidor';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'contribuyente_id', 'fecha_inicio', 'fecha_fin', 'monto', 'estado'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
