<?php

namespace App\Models;

use CodeIgniter\Model;

class PdtModel extends Model
{
    protected $table      = 'pdt';
    protected $primaryKey = 'id_pdt';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id_pdt', 'pdt_descripcion', 'pdt_estado', 'id_declaracion'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
