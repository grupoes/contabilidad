<?php

namespace App\Models;

use CodeIgniter\Model;

class ArchivosAfpModel extends Model
{
    protected $table      = 'archivos_afp';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'afp_id', 'archivo_reporte', 'archivo_ticket', 'archivo_plantilla', 'estado', 'user_add', 'user_edit', 'user_delete', 'deleted_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
