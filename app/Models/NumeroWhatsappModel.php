<?php

namespace App\Models;

use CodeIgniter\Model;

class NumeroWhatsappModel extends Model
{
    protected $table      = 'numeros_whatsapp';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'titulo', 'numero', 'link', 'estado'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
