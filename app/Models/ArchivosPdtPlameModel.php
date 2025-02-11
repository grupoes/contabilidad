<?php namespace App\Models;

    use CodeIgniter\Model;

    class ArchivosPdtPlameModel extends Model
    {
        protected $table      = 'archivos_pdtplame';
        protected $primaryKey = 'id_archivos_pdtplame';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_archivos_pdtplame','id_pdtplame', 'archivo_planilla', 'archivo_honorarios', 'archivo_constancia', 'estado', 'user_id', 'user_edit', 'user_delete'];

        protected $useTimestamps = true;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>