<?php

namespace App\Models;

use CodeIgniter\Model;

class ComunicacionBajaModel extends Model
{
    protected $table      = 'comunicacion_baja';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'name_file_xml_cpe', 'name_file_zip_cpe', 'ruta_cpe_zip', 'file_cpe_zip', 'hash_cpe', 'respuesta', 'codigo_ticket', 'cod_sunat', 'hash_cdr', 'msj_sunat', 'name_file_xml_cdr', 'name_file_zip_cdr', 'ruta_cdr_zip', 'file_cdr_zip', 'titulo', 'id_factura'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
