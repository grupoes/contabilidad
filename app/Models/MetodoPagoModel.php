<?php namespace App\Models;

    use CodeIgniter\Model;

    class MetodoPagoModel extends Model
    {
        protected $table      = 'metodos_pagos';
        protected $primaryKey = 'id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['id','metodo', 'descripcion', 'estado'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>