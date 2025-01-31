<?php namespace App\Models;

    use CodeIgniter\Model;

    class DeclaracionSunatModel extends Model
    {
        protected $table      = 'declaracion_sunat';
        protected $primaryKey = 'id_declaracion_sunat';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_declaracion_sunat','decl_sunat_codigo', 'decl_sunat_importe_venta', 'decl_sunat_importe_compra', 'id_fecha_declaracion', 'contribuyente_id', 'fecha_registro', 'fecha_ingreso', 'monto', 'decl_porcentaje'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>