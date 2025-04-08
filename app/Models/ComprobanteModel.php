<?php

namespace App\Models;

use CodeIgniter\Model;

class ComprobanteModel extends Model
{
    protected $table      = 'comprobante';
    protected $primaryKey = 'id_comprobante';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id_comprobante', 'id_tipo_moneda', 'id_tipo_comprobante', 'comprobante_documento_serie_caracteristicas', 'comprobante_ruc', 'comprobante_nombre_razon', 'comprobante_venta', 'comprobante_tipo_cambio', 'comprobante_tipo_estado', 'comprobante_condicion', 'ruc_empresa_numero', 'comprobante_documento_serie_numero', 'comprobante_fecha'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
