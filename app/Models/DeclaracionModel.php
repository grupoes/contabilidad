<?php

namespace App\Models;

use CodeIgniter\Model;

class DeclaracionModel extends Model
{
    protected $table      = 'declaracion';
    protected $primaryKey = 'id_declaracion';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id_declaracion', 'decl_nombre', 'decl_estado', 'decl_descripcion', 'decl_color'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
