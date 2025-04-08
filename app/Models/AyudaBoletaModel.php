<?php

namespace App\Models;

use CodeIgniter\Model;

class AyudaBoletaModel extends Model
{
    protected $table      = 'ayuda_boleta';
    protected $primaryKey = 'id_ayuda_boleta';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id_ayuda_boleta', 'serie_caracteristica', 'serie_numero', 'id_ruc_empresa', 'monton', 'fecha', 'ruc_cliente'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
