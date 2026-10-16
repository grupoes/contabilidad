<?php namespace App\Models;

    use CodeIgniter\Model;

    class NotariaModel extends Model
    {
        protected $table      = 'notaria';
        protected $primaryKey = 'not_id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['not_id', 'not_ruc', 'not_razon_social', 'not_estado'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';
    }

?>
