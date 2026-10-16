<?php namespace App\Models;

    use CodeIgniter\Model;

    class StanModel extends Model
    {
        protected $table      = 'stan';
        protected $primaryKey = 'stan_id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['stan_id', 'stan_sede', 'sede_id', 'stan_numero', 'stan_estado'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';
    }

?>
