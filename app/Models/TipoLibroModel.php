<?php namespace App\Models;

    use CodeIgniter\Model;

    class TipoLibroModel extends Model
    {
        protected $table      = 'tipo_libro';
        protected $primaryKey = 'tli_id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['tli_id', 'tli_nombre', 'tli_estado'];

        protected $useTimestamps = false;
    }

?>
