<?php namespace App\Models;

    use CodeIgniter\Model;

    class ProfileModel extends Model
    {
        protected $table      = 'perfil';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','nombre_perfil', 'estado'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

        public function getAllProfiles()
        {
            return $this->where('id !=', 1)->findAll();
        }

    }

?>