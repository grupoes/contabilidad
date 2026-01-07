<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');

        if (!$header) {
            return service('response')
                ->setJSON(['status' => false, 'message' => 'Token requerido'])
                ->setStatusCode(401);
        }

        if (!preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return service('response')
                ->setJSON(['status' => false, 'message' => 'Formato de token inválido'])
                ->setStatusCode(401);
        }

        $token = $matches[1];
        $key = getenv('JWT_SECRET');

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // Guardar datos del usuario en la request
            $request->user = $decoded;
        } catch (\Exception $e) {
            return service('response')
                ->setJSON(['status' => false, 'message' => 'Token inválido o expirado'])
                ->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita nada aquí
    }
}
