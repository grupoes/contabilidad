<?php namespace App\Models;

    use CodeIgniter\Model;

    class LibroSimulacionModel extends Model
    {
        protected $table      = 'libro_simulacion';
        protected $primaryKey = 'lsi_id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['lsi_id', 'lib_id', 'lsi_periodo', 'lsi_hojas', 'lsi_estado', 'lsi_user_add', 'lsi_fecha_add', 'lsi_user_delete', 'lsi_fecha_delete'];

        protected $useTimestamps = false;
    }

?>
