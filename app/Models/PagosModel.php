<?php namespace App\Models;

    use CodeIgniter\Model;

    class PagosModel extends Model
    {
        protected $table      = 'pagos';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','contribuyente_id', 'fecha_pago', 'monto_total', 'mesCorrespondiente', 'montoPagado', 'montoPendiente', 'montoExcedente', 'usuario_id_cobra', 'estado'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>