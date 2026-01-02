<?php

namespace App\Models;

use CodeIgniter\Model;

class TrabajadoresContriModel extends Model
{
    protected $table      = 'trabajadores_contribuyentes';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'numero_documento', 'tipo_documento', 'nombres', 'estado', 'password'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
