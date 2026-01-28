<?php

namespace App\Models;

use CodeIgniter\Model;

class FoldersFilesModel extends Model
{
    protected $table      = 'foldersfiles';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'idfolderfile', 'name', 'tipo', 'parentId', 'estado'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
