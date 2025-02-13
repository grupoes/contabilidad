<?php namespace App\Models;

    use CodeIgniter\Model;

    class ArchivosBoletaPagoModel extends Model
    {
        protected $table      = 'archivos_boleta';
        protected $primaryKey = 'id_archivo_boleta';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_archivo_boleta','id_boleta', 'archivo', 'estado', 'user_id', 'user_edit', 'user_delete'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>