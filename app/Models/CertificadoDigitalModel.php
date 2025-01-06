<?php namespace App\Models;

    use CodeIgniter\Model;

    class CertificadoDigitalModel extends Model
    {
        protected $table      = 'certificado_digital';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','tipo_certificado', 'fecha_inicio', 'fecha_vencimiento', 'clave', 'ruta', 'nameFile', 'estado', 'contribuyente_id'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>