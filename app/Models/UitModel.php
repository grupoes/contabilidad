<?php namespace App\Models;

    use CodeIgniter\Model;

    class UitModel extends Model
    {
        protected $table      = 'uit';
        protected $primaryKey = 'id_uit';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_uit','uit_monto','created_at','updated_at'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>