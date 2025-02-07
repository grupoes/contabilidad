<?php namespace App\Models;

    use CodeIgniter\Model;

    class ArchivosPdt0621Model extends Model
    {
        protected $table      = 'archivos_pdt0621';
        protected $primaryKey = 'id_archivos_pdt';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_archivos_pdt','id_pdt_renta', 'nombre_pdt', 'nombre_constancia', 'estado', 'user_id', 'user_edit', 'user_delete'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

        public function getAllProfiles()
        {
            return $this->where('id !=', 1)->findAll();
        }

    }

?>