<?php

namespace App\Models;

use CodeIgniter\Model;

class PagoAmortizacionServidorModel extends Model
{
    protected $table      = 'pagos_servidor_amortizacion';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'contribuyente_id', 'movimientoId', 'registro', 'fecha', 'fecha_pago', 'metodo_pago_id', 'monto', 'voucher', 'estado'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
