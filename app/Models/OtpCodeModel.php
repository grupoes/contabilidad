<?php

namespace App\Models;

use CodeIgniter\Model;

class OtpCodeModel extends Model
{
    protected $table      = 'otp_codes';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id',
        'user_type',
        'codigo',
        'intentos',
        'usado',
        'expires_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ---------------------------------------------------------------
    // Helpers de negocio
    // ---------------------------------------------------------------

    /**
     * Invalida todos los OTPs activos anteriores del usuario
     * y crea uno nuevo con 10 minutos de vigencia.
     */
    public function crearCodigo(int $userId, string $userType, int $codigo): array
    {
        // Invalidar registros anteriores del mismo usuario
        $this->where('user_id', $userId)
             ->where('user_type', $userType)
             ->where('usado', false)
             ->set(['usado' => true])
             ->update();

        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $id = $this->insert([
            'user_id'    => $userId,
            'user_type'  => $userType,
            'codigo'     => (string) $codigo,
            'intentos'   => 0,
            'usado'      => false,
            'expires_at' => $expires,
        ]);

        return $this->find($id);
    }

    /**
     * Busca el OTP vigente (no usado, no expirado) de un usuario.
     */
    public function obtenerVigente(int $userId, string $userType): ?array
    {
        return $this->where('user_id', $userId)
                    ->where('user_type', $userType)
                    ->where('usado', false)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    /**
     * Verifica un código ingresado por el usuario.
     *
     * Retorna un array con:
     *   - success (bool)
     *   - message (string)
     *   - intentos_restantes (int|null)  — null si fue exitoso o bloqueado
     */
    public function verificarCodigo(int $userId, string $userType, string $codigoIngresado): array
    {
        $otp = $this->obtenerVigente($userId, $userType);

        if (!$otp) {
            return [
                'success'           => false,
                'message'           => 'Código expirado o inválido. Solicita uno nuevo.',
                'intentos_restantes' => null,
            ];
        }

        // Límite de intentos alcanzado
        if ($otp['intentos'] >= 3) {
            $this->update($otp['id'], ['usado' => true]);
            return [
                'success'           => false,
                'message'           => 'Demasiados intentos. Solicita un nuevo código.',
                'intentos_restantes' => 0,
            ];
        }

        // Código incorrecto
        if ($otp['codigo'] !== $codigoIngresado) {
            $nuevosIntentos = $otp['intentos'] + 1;
            $this->update($otp['id'], ['intentos' => $nuevosIntentos]);

            $restantes = 3 - $nuevosIntentos;

            if ($restantes === 0) {
                $this->update($otp['id'], ['usado' => true]);
            }

            return [
                'success'           => false,
                'message'           => 'Código incorrecto.',
                'intentos_restantes' => $restantes,
            ];
        }

        // Código correcto
        $this->update($otp['id'], ['usado' => true]);

        return [
            'success'           => true,
            'message'           => 'Código verificado correctamente.',
            'intentos_restantes' => null,
        ];
    }
}
