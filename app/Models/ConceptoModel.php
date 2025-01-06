<?php namespace App\Models;

    use CodeIgniter\Model;

    class ConceptoModel extends Model
    {
        protected $table      = 'concepto';
        protected $primaryKey = 'con_id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['con_id','id_tipo_movimiento', 'con_descripcion', 'con_estado'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>