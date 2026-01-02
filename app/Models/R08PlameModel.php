<?php

namespace App\Models;

use CodeIgniter\Model;

class R08PlameModel extends Model
{
    protected $table      = 'r08_plame';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'plameId', 'nameFile', 'status', 'user_id', 'user_edit', 'user_delete', 'fecha_ingreso', 'numero_documento', 'tipo_documento', 'nombres', 'situacion', 'ruc', 'read_boleta'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
