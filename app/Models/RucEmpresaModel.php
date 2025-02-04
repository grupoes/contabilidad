<?php namespace App\Models;

    use CodeIgniter\Model;

    class RucEmpresaModel extends Model
    {
        protected $table      = 'ruc_empresa';
        protected $primaryKey = 'ruc_empresa_numero';

        protected $useAutoIncrement = false;

        protected $returnType     = 'array';

        protected $allowedFields = ['ruc_empresa_numero','ruc_empresa_razon_social', 'ruc_empresa_monto', 'gratuito', 'tipo_de_pago', 'tipo_servicio', 'ruc_empresa_estado', 'costo_anual', ''];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>