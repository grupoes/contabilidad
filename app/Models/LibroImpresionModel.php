<?php namespace App\Models;

    use CodeIgniter\Model;

    class LibroImpresionModel extends Model
    {
        protected $table      = 'libro_impresion';
        protected $primaryKey = 'lim_id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['lim_id', 'lib_id', 'lim_folio_desde', 'lim_folio_hasta', 'lim_periodo_desde', 'lim_periodo_hasta', 'lim_observacion', 'lim_estado', 'lim_user_add', 'lim_fecha_add', 'lim_user_delete', 'lim_fecha_delete'];

        protected $useTimestamps = false;
    }

?>
