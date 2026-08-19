<?php

namespace App\Models;

use CodeIgniter\Model;

class BajaLoteDocumentoModel extends Model
{
    protected $DBGroup    = 'facturador';
    protected $table      = 'baja_lote_documento';
    protected $primaryKey = 'id_baja_lote_documento';

    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'id_baja_lote_documento', 'id_baja_lote', 'serie_comprobante',
        'numero_comprobante', 'fecha_comprobante', 'total', 'estado',
    ];

    protected $useTimestamps = false;
}
