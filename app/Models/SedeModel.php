<?php namespace App\Models;

    use CodeIgniter\Model;

    class SedeModel extends Model
    {
        protected $table      = 'sede';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','empresa_id', 'nombre_sede', 'direccion_sede', 'anexo', 'estado'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

        public function allSedes()
        {
            return $this->where('estado', 1)->findAll();
        }

    }

?>