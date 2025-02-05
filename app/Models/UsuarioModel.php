<?php namespace App\Models;

    use CodeIgniter\Model;

    class UsuarioModel extends Model
    {
        protected $table      = 'usuario_c';
        protected $primaryKey = 'usu_id';

        protected $useAutoIncrement = true;

        protected $returnType     = 'array';

        protected $allowedFields = ['usu_id','usu_usuario','usu_clave', 'usu_fechareg', 'usu_perfil', 'usu_estado','usu_sede', 'dni', 'nombres', 'apellidos', 'telefono', 'correo', 'direccion', 'numero_bancario', 'fecha_nacimiento'];

        protected $useTimestamps = false;
        protected $createdField  = 'created_at';
        protected $updatedField  = 'updated_at';

    }

?>