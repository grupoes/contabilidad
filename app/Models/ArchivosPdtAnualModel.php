<?php namespace App\Models;

    use CodeIgniter\Model;

    class ArchivosPdtAnualModel extends Model
    {
        protected $table      = 'archivos_pdtanual';
        protected $primaryKey = 'id_archivo_anual';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_archivo_anual','id_pdt_anual', 'pft', 'constancia', 'estado', 'user_id', 'user_edit', 'user_delete'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>