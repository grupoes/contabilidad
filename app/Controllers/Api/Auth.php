<?php

namespace App\Controllers\Api;

use App\Models\ContribuyenteModel;
use CodeIgniter\RESTful\ResourceController;
use App\Models\TrabajadoresContriModel;
use Firebase\JWT\JWT;

class Auth extends ResourceController
{
    protected $format = 'json';

    public function login()
    {
        $data = $this->request->getJSON(true);

        if (!isset($data['username']) || !isset($data['password'])) {
            return $this->respond([
                'status' => false,
                'message' => 'Usuario y contraseña son requeridos'
            ], 400);
        }

        if (strlen($data['username']) == 11) {
            $contri = new ContribuyenteModel();

            $user = $contri->where('ruc', $data['username'])->first();

            if (!$user) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            if ($data['password'] !== $user['acceso']) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Contraseña incorrecta'
                ], 401);
            }

            // Crear JWT
            $key = getenv('JWT_SECRET');
            $payload = [
                'iat' => time(),             // Fecha emisión
                'exp' => time() + 3600,      // Expira en 1 hora
                'uid' => $user['id'],        // ID de usuario
                'username' => $user['ruc'],
                'role' => 'contribuyente'
            ];

            $token = JWT::encode($payload, $key, 'HS256');

            return $this->respond([
                'status' => true,
                'message' => 'Login exitoso',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'nombre' => $user['razon_social'],
                    'username' => $user['ruc'],
                    'role' => 'contribuyente'
                ]
            ]);
        } else {
            $userModel = new TrabajadoresContriModel();
            $user = $userModel->where('numero_documento', $data['username'])->first();

            if (!$user) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            if ($data['password'] !== $user['password']) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Contraseña incorrecta'
                ], 401);
            }

            // Crear JWT
            $key = getenv('JWT_SECRET');
            $payload = [
                'iat' => time(),             // Fecha emisión
                'exp' => time() + 3600,      // Expira en 1 hora
                'uid' => $user['id'],        // ID de usuario
                'username' => $user['numero_documento'],
                'role' => 'trabajador'
            ];

            $token = JWT::encode($payload, $key, 'HS256');

            return $this->respond([
                'status' => true,
                'message' => 'Login exitoso',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'nombre' => $user['nombres'],
                    'username' => $user['numero_documento'],
                    'role' => 'trabajador'
                ]
            ]);
        }
    }
}
