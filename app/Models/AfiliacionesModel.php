<?php

namespace App\Models;

use CodeIgniter\Model;

class AfiliacionesModel extends Model
{
    protected $table      = 'afiliaciones';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'contribuyente_id', 'fecha_inicio', 'fecha_fin'];

    protected $useTimestamps = false;
}
