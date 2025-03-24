<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefijosModel extends Model
{
    protected $table      = 'prefijos_paises';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'pais', 'codigo', 'digitos'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
