<?php

namespace App\Models;

use CodeIgniter\Model;

class FeriadoModel extends Model
{
    protected $table = 'feriados';
    protected $primaryKey = 'id';
    protected $allowedFields = ['fecha', 'descripcion'];
    protected $useTimestamps = false;
}
