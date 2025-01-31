<?php namespace App\Models;

    use CodeIgniter\Model;

    class ContribuyenteModel extends Model
    {
        protected $table      = 'contribuyentes';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','ruc', 'razon_social', 'nombre_comercial', 'direccion_fiscal', 'ubigeo_id', 'urbanizacion', 'tipoSuscripcion', 'tipoServicio', 'tipoPago', 'costoMensual', 'costoAnual', 'diaCobro', 'fechaContrato', 'telefono', 'correo', 'usuario_secundario', 'clave_usuario_secundario', 'acceso', 'user_add', 'user_edit', 'user_delete', 'estado', 'ruc_empresa_normal', 'ruc_empresa_baja', 'ruc_empresa_medio'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>