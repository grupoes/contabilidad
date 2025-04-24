<?php

namespace App\Models;

use CodeIgniter\Model;

class EnviosModel extends Model
{
    protected $table      = 'envios';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'mensaje_id', 'contacto_id', 'message', 'fecha_envio', 'estado', 'intentos', 'numero_whatsapp', 'nombre_contacto', 'razon_social', 'ruc', 'link'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
