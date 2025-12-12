<?php

namespace App\Models;

use CodeIgniter\Model;

class MigrarModel extends Model
{
    protected $table      = 'migrar';
    protected $primaryKey = 'id_migrar';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id_migrar', 'id_migracion', 'fecha', 'serie', 'numero', 'ruc', 'tipo', 'comprobante_tipo', 'valor_venta', 'igv', 'icbper', 'monto', 'ruc_empresa', 'razon_social'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
