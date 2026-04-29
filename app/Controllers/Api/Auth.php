<?php

namespace App\Controllers\Api;

use App\Models\ContribuyenteModel;
use CodeIgniter\RESTful\ResourceController;
use App\Models\TrabajadoresContriModel;
use App\Models\OtpCodeModel;
use Firebase\JWT\JWT;

class Auth extends ResourceController
{
    protected $format = 'json';

    public function login()
    {
        try {
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
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'No se puede hacer la consulta ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetPasswordLink()
    {
        try {
            $data = $this->request->getJSON(true);

            $email = $data['email'] ?? null;
            $id_user = $data['id_user'] ?? null;
            $username = $data['username'] ?? null;

            // Si no tenemos email, lo buscamos por id_user y username (flujo perfil logueado)
            if (!$email && $id_user && $username) {
                $userType = (strlen($username) == 11) ? 'contribuyente' : 'trabajador';
                if ($userType === 'contribuyente') {
                    $user = (new ContribuyenteModel())->find($id_user);
                } else {
                    $user = (new TrabajadoresContriModel())->find($id_user);
                }
                $email = $user['correo'] ?? null;
                
                if (!$email) {
                    return $this->respond([
                        'status' => false,
                        'message' => 'El usuario no tiene un correo registrado para recibir el código.'
                    ], 404);
                }
            }

            if (!$email) {
                return $this->respond([
                    'status' => false,
                    'message' => 'El correo electrónico es requerido'
                ], 400);
            }

            // Si no tenemos id_user o username, buscamos por correo (flujo Olvidé mi contraseña)
            if (!$id_user || !$username) {
                $contriModel = new ContribuyenteModel();
                $user = $contriModel->where('correo', $email)->first();
                $userType = 'contribuyente';

                if (!$user) {
                    $trabModel = new TrabajadoresContriModel();
                    $user = $trabModel->where('correo', $email)->first();
                    $userType = 'trabajador';
                }

                if (!$user) {
                    return $this->respond([
                        'status' => false,
                        'message' => 'No se encontró ningún usuario asociado a este correo electrónico.'
                    ], 404);
                }

                $id_user = $user['id'];
                $username = ($userType === 'contribuyente') ? $user['ruc'] : $user['numero_documento'];
            } else {
                $userType = (strlen($username) == 11) ? 'contribuyente' : 'trabajador';
            }

            // Generar código OTP de 6 dígitos
            $codigo = random_int(100000, 999999);

            // Actualizar correo si se proporcionó uno nuevo (para el flujo de primer login)
            if (isset($data['id_user'])) {
                if ($userType === 'contribuyente') {
                    (new ContribuyenteModel())->update($id_user, ['correo' => $email]);
                } else {
                    (new TrabajadoresContriModel())->update($id_user, ['correo' => $email]);
                }
            }

            // Guardar el código en la tabla otp_codes
            $otpModel = new OtpCodeModel();
            $otpModel->crearCodigo($id_user, $userType, $codigo);

            // Enviar el correo
            $emailService = \Config\Services::email();
            
            // Configuración rápida del correo (puedes ajustar esto según tu .env)
            $emailService->setFrom(env('email.SMTPUser', 'no-reply@tuempresa.com'), 'Sistema de Contabilidad');
            $emailService->setTo($email);
            $emailService->setSubject('Código de Verificación - Recuperación de Contraseña');
            
            // Formatear el código para que se vea mejor (ej: 123 456)
            $codigoFormateado = substr($codigo, 0, 3) . ' ' . substr($codigo, 3);
            
            $mensaje = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #333; text-align: center;'>Recuperación de Contraseña</h2>
                    <p>Hola,</p>
                    <p>Has solicitado restablecer tu contraseña. Utiliza el siguiente código de 6 dígitos para continuar con el proceso:</p>
                    <div style='background-color: #f4f4f4; padding: 20px; text-align: center; border-radius: 5px;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #007bff;'>$codigoFormateado</span>
                    </div>
                    <p style='margin-top: 20px;'>Este código expirará en 10 minutos.</p>
                    <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
                    <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #777; text-align: center;'>&copy; " . date('Y') . " Grupo ES Consultores. Todos los derechos reservados.</p>
                </div>
            ";
            
            $emailService->setMessage($mensaje);
            $emailService->setMailType('html');

            if ($emailService->send()) {
                return $this->respond([
                    'status' => true,
                    'message' => 'Código de verificación enviado correctamente a ' . $email,
                    'id_user' => $id_user,
                    'username' => $username
                ]);
            } else {
                return $this->respond([
                    'status' => false,
                    'message' => 'Error al enviar el correo: ' . $emailService->printDebugger(['headers'])
                ], 500);
            }

        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyCode()
    {
        try {
            $data = $this->request->getJSON(true);

            if (!isset($data['code']) || !isset($data['id_user']) || !isset($data['username'])) {
                return $this->respond([
                    'status'  => false,
                    'message' => 'Código, ID de usuario y username son requeridos'
                ], 400);
            }

            $code     = (string) $data['code'];
            $id_user  = (int) $data['id_user'];
            $username = (string) $data['username'];

            $userType = (strlen($username) == 11) ? 'contribuyente' : 'trabajador';

            $otpModel  = new OtpCodeModel();
            $resultado = $otpModel->verificarCodigo($id_user, $userType, $code);

            if (!$resultado['success']) {
                return $this->respond([
                    'status'             => false,
                    'message'            => $resultado['message'],
                    'intentos_restantes' => $resultado['intentos_restantes'],
                ], 400);
            }

            return $this->respond([
                'status'  => true,
                'message' => $resultado['message'],
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status'  => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    public function updatePassword()
    {
        try {
            $data = $this->request->getJSON(true);

            if (!isset($data['newPassword']) || !isset($data['confirmPassword']) || !isset($data['id_user']) || !isset($data['username'])) {
                return $this->respond([
                    'status'  => false,
                    'message' => 'Todos los campos son requeridos'
                ], 400);
            }

            $newPassword     = (string) $data['newPassword'];
            $confirmPassword = (string) $data['confirmPassword'];
            $id_user         = (int) $data['id_user'];
            $username        = (string) $data['username'];

            // Validación: Mínimo 8 caracteres
            if (strlen($newPassword) < 8) {
                return $this->respond([
                    'status'  => false,
                    'message' => 'La contraseña debe tener al menos 8 caracteres'
                ], 400);
            }

            // Validación: Alfanumérico
            if (!preg_match('/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9]+$/', $newPassword)) {
                return $this->respond([
                    'status'  => false,
                    'message' => 'La contraseña debe ser alfanumérica (letras y números)'
                ], 400);
            }

            if ($newPassword !== $confirmPassword) {
                return $this->respond([
                    'status'  => false,
                    'message' => 'Las contraseñas no coinciden'
                ], 400);
            }

            if (strlen($username) == 11) {
                // Contribuyente
                $model = new ContribuyenteModel();
                $update = $model->update($id_user, ['acceso' => $newPassword]);
            } else {
                // Trabajador
                $model = new TrabajadoresContriModel();
                $update = $model->update($id_user, ['password' => $newPassword]);
            }

            if (!$update) {
                return $this->respond([
                    'status'  => false,
                    'message' => 'No se pudo actualizar la contraseña en la base de datos'
                ], 500);
            }

            // Generar token para que el frontend pueda iniciar sesión automáticamente
            $key = getenv('JWT_SECRET');
            $payload = [
                'iat' => time(),
                'exp' => time() + 3600,
                'uid' => $id_user,
                'username' => $username,
                'role' => (strlen($username) == 11) ? 'contribuyente' : 'trabajador'
            ];
            $token = JWT::encode($payload, $key, 'HS256');

            // Obtener datos básicos del usuario para la sesión del frontend
            if (strlen($username) == 11) {
                $userData = $model->find($id_user);
                $nombre = $userData['razon_social'];
            } else {
                $userData = $model->find($id_user);
                $nombre = $userData['nombres'];
            }

            return $this->respond([
                'status'  => true,
                'message' => 'Contraseña actualizada correctamente',
                'token'   => $token,
                'user'    => [
                    'id'       => $id_user,
                    'nombre'   => $nombre,
                    'username' => $username,
                    'role'     => (strlen($username) == 11) ? 'contribuyente' : 'trabajador'
                ]
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status'  => false,
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
