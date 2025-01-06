<?php namespace App\Models;

    use CodeIgniter\Model;

    class UbigeoModel extends Model
    {
        protected $table      = 'sunat_codigoubigeo';
        protected $primaryKey = 'codigo_ubigeo';

        protected $useAutoIncrement = false;

        protected $returnType     = 'array';

        protected $allowedFields = ['codigo_ubigeo','departamento', 'provincia', 'distrito'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

        public function allUbigeo()
        {
            return $this->findAll();
        }

    }

?>