<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'usuario';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';

    protected $allowedFields = ['id', 'correo', 'username', 'password', 'alias', 'perfil_id', 'sede_id', 'tipo_documento_id', 'numero_documento', 'nombres', 'apellidos', 'telefono', 'direccion', 'fecha_nacimiento', 'numero_cuenta', 'estado', 'path'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getUserByUsername($username)
    {
        return $this->select('usuario.*, perfil.id as idperfil, perfil.nombre_perfil')->join('perfil', 'perfil.id = usuario.perfil_id')->where('usuario.username', $username)->where('usuario.estado', 1)->first();
    }

    public function getUserByEmail($email)
    {
        return $this->where('correo', $email)->first();
    }

    public function usersAll()
    {
        return $this->select('usuario.id, usuario.correo, usuario.username, usuario.alias, usuario.perfil_id, usuario.sede_id, usuario.tipo_documento_id, usuario.numero_documento, usuario.nombres, usuario.apellidos, usuario.telefono, usuario.direccion, usuario.fecha_nacimiento, usuario.numero_cuenta, usuario.estado, usuario.path, perfil.nombre_perfil')->join('perfil', 'perfil.id = usuario.perfil_id')->where('usuario.perfil_id !=', 1)->where('usuario.estado', 1)->findAll();
    }
}
