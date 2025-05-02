<?php

namespace App\Models;

use CodeIgniter\Model;

class MovimientoModel extends Model
{
    protected $table      = 'movimiento';
    protected $primaryKey = 'mov_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['mov_id', 'id_sesion_caja', 'mov_formapago', 'id_metodo_pago', 'mov_concepto', 'mov_fecha', 'mov_monto', 'mov_estado', 'mov_descripcion', 'mov_hora', 'id_tipo_comprobante', 'tipo_comprobante_descripcion', 'mov_cobro', 'userRegister', 'nombreUser'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
