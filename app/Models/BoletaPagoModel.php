<?php namespace App\Models;

    use CodeIgniter\Model;

    class BoletaPagoModel extends Model
    {
        protected $table      = 'boleta_pago';
        protected $primaryKey = 'id_boleta';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_boleta','ruc_empresa', 'periodo', 'anio', 'user_id', 'user_edit', 'user_delete', 'estado'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>