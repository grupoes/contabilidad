<?php

namespace App\Models;

use CodeIgniter\Model;

class SireModel extends Model
{
    protected $table      = 'sire';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'contribuyente_id', 'periodo', 'anio', 'estado', 'user_add', 'user_edit', 'user_delete', 'deleted_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
