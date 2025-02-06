<?php namespace App\Models;

    use CodeIgniter\Model;

    class ContribuyentesUsuarioModel extends Model
    {
        protected $table      = 'contribuyentes_usuario';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','contribuyente_id', 'usuario_id'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>