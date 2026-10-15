<?php namespace App\Models;

    use CodeIgniter\Model;

    class LibroImpresionModel extends Model
    {
        protected $table      = 'libro_impresion';
        protected $primaryKey = 'lim_id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['lim_id', 'lib_id', 'lim_folio_desde', 'lim_folio_hasta', 'lim_periodo_desde', 'lim_periodo_hasta', 'lim_estado'];

        protected $useTimestamps = false;
    }

?>
