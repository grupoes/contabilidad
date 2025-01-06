<?php namespace App\Models;

    use CodeIgniter\Model;

    class SistemaModel extends Model
    {
        protected $table      = 'sistemas';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','nameSystem', 'description', 'status'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>