<?php namespace App\Models;

    use CodeIgniter\Model;

    class CodificacionModel extends Model
    {
        protected $table      = 'codificacion';
        protected $primaryKey = 'id_codificacion';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_codificacion','contribuyente_id', 'id_tipo_comprobante', 'id_codigo_tipo', 'codificacion_numero'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>