<?php

namespace App\Models;

use CodeIgniter\Model;

class MigracionModel extends Model
{
    protected $table      = 'migracion';
    protected $primaryKey = 'id_migracion';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id_migracion', 'nombre_archivo'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
