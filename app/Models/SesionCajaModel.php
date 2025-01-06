<?php namespace App\Models;

    use CodeIgniter\Model;

    class SesionCajaModel extends Model
    {
        protected $table      = 'sesion_caja';
        protected $primaryKey = 'id_sesion_caja';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id_sesion_caja','id_usuario', 'id_sede_caja', 'ses_fechaapertura', 'ses_montoapertura', 'ses_montocierre', 'ses_estado', 'ses_fechacierre'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>