<?php namespace App\Models;

    use CodeIgniter\Model;

    class AnioModel extends Model
    {
        protected $table      = 'anio';
        protected $primaryKey = 'id_anio';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_anio','anio_descripcion', 'anio_estado', 'anio_color'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>