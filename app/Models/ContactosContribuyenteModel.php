<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactosContribuyenteModel extends Model
{
    protected $table      = 'contacto_contribuyente';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'nombre_contacto', 'telefono', 'prefijo', 'numero_whatsapp', 'correo', 'estado', 'contribuyente_id'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
