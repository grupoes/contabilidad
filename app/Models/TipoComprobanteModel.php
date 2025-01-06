<?php namespace App\Models;

    use CodeIgniter\Model;

    class TipoComprobanteModel extends Model
    {
        protected $table      = 'tipo_comprobante';
        protected $primaryKey = 'id_tipo_comprobante';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_tipo_comprobante','tipo_comprobante_descripcion', 'tipo_comprobante_estado', 'tipo_comprobante_nombre'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>