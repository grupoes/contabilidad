<?php namespace App\Models;

    use CodeIgniter\Model;

    class SistemaContribuyenteModel extends Model
    {
        protected $table      = 'sistemas_contribuyente';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','contribuyente_id', 'system_id'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>