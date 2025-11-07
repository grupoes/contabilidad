<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table      = 'servicio';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'metodo_id', 'comprobante_id', 'ruc', 'razon_social', 'monto', 'descripcion', 'estado', 'created_at', 'updated_at', 'url_pdf', 'url_ticket', 'user_add', 'user_edit', 'user_delete'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
