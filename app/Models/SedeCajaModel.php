<?php namespace App\Models;

    use CodeIgniter\Model;

    class SedeCajaModel extends Model
    {
        protected $table      = 'sede_caja';
        protected $primaryKey = 'id_sede_caja';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_sede_caja','id_caja', 'id_sede', 'sede_caja_monto'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>