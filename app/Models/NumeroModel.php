<?php

namespace App\Models;

use CodeIgniter\Model;

class NumeroModel extends Model
{
    protected $table      = 'numero';
    protected $primaryKey = 'id_numero';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id_numero', 'num_descripcion', 'num_estado'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
