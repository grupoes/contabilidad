<?php namespace App\Models;

    use CodeIgniter\Model;

    class tributoModel extends Model
    {
        protected $table      = 'tributo';
        protected $primaryKey = 'id_tributo';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_tributo','tri_descripcion','tri_estado', 'id_pdt', 'tri_tipo', 'tri_codigo', 'tipo', 'porcentaje_renta', 'porcentaje_renta_segunda'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>