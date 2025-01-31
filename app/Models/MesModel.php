<?php namespace App\Models;

    use CodeIgniter\Model;

    class MesModel extends Model
    {
        protected $table      = 'mes';
        protected $primaryKey = 'id_mes';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_mes','mes_descripcion', 'mes_estado', 'mes_id_mes', 'mes_declaracion', 'mes_fecha'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>