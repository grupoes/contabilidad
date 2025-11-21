<?php

namespace App\Models;

use CodeIgniter\Model;

class ArchivoTextZipSireModel extends Model
{
    protected $table      = 'archivos_txt_zip_sire';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'sire_id', 'name_file', 'estado', 'user_add', 'user_edit', 'user_delete', 'deleted_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
