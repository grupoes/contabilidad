<?php namespace App\Models;

    use CodeIgniter\Model;

    class ContadorModel extends Model
    {
        protected $table      = 'contador';
        protected $primaryKey = 'id_contador';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_contador','nombre_apellidos', 'dni', 'numero_colegiatura', 'domicilio', 'estado', 'ubigeo'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>