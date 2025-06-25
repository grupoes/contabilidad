<?php

namespace App\Models;

use CodeIgniter\Model;

class HonorariosModel extends Model
{
    protected $table      = 'honorarios';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'mes', 'year', 'descripcion', 'estado'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
