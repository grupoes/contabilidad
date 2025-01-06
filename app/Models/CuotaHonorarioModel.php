<?php namespace App\Models;

    use CodeIgniter\Model;

    class CuotaHonorarioModel extends Model
    {
        protected $table      = 'cuota_honorario';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','contribuyente_id', 'cuo_nrocuota', 'cuo_fechavence', 'cuo_fechacancelado', 'cuo_montocuota', 'cuo_montopagado', 'cuo_estado'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>