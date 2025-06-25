<?php

namespace App\Models;

use CodeIgniter\Model;

class FacturasHonorariosModel extends Model
{
    protected $table      = 'facturas_honorarios';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'honorario_id', 'contribuyente_id', 'tipo_doc', 'serie_comprobante', 'numero_comprobante', 'tipo_envio_sunat', 'titulo', 'mensaje', 'url_absoluta_a4', 'url_absoluta_ticket', 'anio', 'mes', 'descripcion', 'estado', 'created_at', 'updated_at'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
