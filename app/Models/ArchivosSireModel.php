<?php

namespace App\Models;

use CodeIgniter\Model;

class ArchivosSireModel extends Model
{
    protected $table      = 'archivos_sire';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'sire_id', 'constancia_ventas', 'constancia_compras', 'detalle_preliminar', 'ajustes_posteriores', 'estado', 'user_add', 'user_edit', 'user_delete', 'deleted_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
