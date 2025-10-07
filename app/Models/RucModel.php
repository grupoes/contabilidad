<?php

namespace App\Models;

use CodeIgniter\Model;

class RucModel extends Model
{
    protected $table      = 'ruc';
    protected $primaryKey = 'id_ruc';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id_ruc', 'ruc_razon_social', 'ruc_estado'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
