<?php

namespace App\Models;

use CodeIgniter\Model;

class BajaLoteModel extends Model
{
    protected $DBGroup     = 'facturador';
    protected $table       = 'baja_lote';
    protected $primaryKey  = 'id_baja_lote';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'id_baja_lote', 'identificador_baja', 'fecha_generacion',
        'estado', 'cantidad_documentos', 'archivo_cdr_zip_base64',
    ];

    protected $useTimestamps = false;
}
