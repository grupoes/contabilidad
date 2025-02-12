<?php namespace App\Models;

    use CodeIgniter\Model;

    class PdtPlameModel extends Model
    {
        protected $table      = 'pdt_plame';
        protected $primaryKey = 'id_pdt_plame';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_pdt_plame','ruc_empresa', 'periodo', 'anio', 'user_id', 'user_edit', 'user_delete', 'estado'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>