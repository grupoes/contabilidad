<?php

namespace App\Models;

use CodeIgniter\Model;

class PdtAnualModel extends Model
{
    protected $table      = 'pdt_anual';
    protected $primaryKey = 'id_pdt_anual';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id_pdt_anual', 'ruc_empresa', 'periodo', 'id_pdt_tipo', 'cargo', 'razon_social', 'monto', 'descripcion', 'link_pdf', 'link_ticket', 'estado_envio', 'user_add', 'user_edit', 'user_delete', 'estado'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
