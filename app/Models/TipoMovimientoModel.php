<?php namespace App\Models;

    use CodeIgniter\Model;

    class TipoMovimientoModel extends Model
    {
        protected $table      = 'tipo_movimiento';
        protected $primaryKey = 'id_tipo_movimiento';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_tipo_movimiento','tipo_movimiento_descripcion','tipo_movimiento_estado'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>