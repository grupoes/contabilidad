<?php namespace App\Models;

    use CodeIgniter\Model;

    class PagosHonorariosModel extends Model
    {
        protected $table      = 'pagos_honorarios';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','contribuyente_id', 'registro', 'fecha', 'metodo_pago_id', 'monto', 'estado'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>