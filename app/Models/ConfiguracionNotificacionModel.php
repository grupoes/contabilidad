<?php namespace App\Models;

    use CodeIgniter\Model;

    class ConfiguracionNotificacionModel extends Model
    {
        protected $table      = 'configuracion_notificacion';
        protected $primaryKey = 'id_tributo';

        protected $useAutoIncrement = false;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_tributo','ruc_empresa_numero',];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>