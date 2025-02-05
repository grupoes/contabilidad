<?php namespace App\Models;

    use CodeIgniter\Model;

    class BancosModel extends Model
    {
        protected $table      = 'bancos';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','nombre_banco', 'moneda', 'nombre_titular', 'numero_cuenta', 'estado'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>