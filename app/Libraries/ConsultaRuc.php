<?php

namespace App\Libraries;

/**
 * Consulta de DNI / RUC en la API externa. Es la misma lógica que usaba Auth::api_dni_ruc,
 * movida aquí para poder reutilizarla (por ejemplo, en la importación de libros).
 * Devuelve el mismo arreglo de respuesta que antes entregaba esa ruta.
 */
class ConsultaRuc
{
    private const TOKEN = 'facturalaya_erickpeso_05jFE7sAOudi8j0';

    /**
     * @param string $tipo      'dni' o 'ruc'
     * @param int    $timeout   segundos de espera de la consulta (400 como antes; la importación usa menos)
     * @param int    $conexion  segundos de espera para conectar (0 = sin límite, como antes)
     */
    public function consultar($tipo, $numero, $timeout = 400, $conexion = 0)
    {
        if ($tipo == 'dni') {
            $ruta = 'https://facturalahoy.com/api/persona/' . $numero . '/' . self::TOKEN . '/completa';
        } elseif ($tipo == 'ruc') {
            $ruta = 'https://facturalahoy.com/api/empresa/' . $numero . '/' . self::TOKEN . '/completa';
        } else {
            return ['respuesta' => 'error', 'titulo' => 'Error', 'mensaje' => 'Tipo de Documento Desconocido'];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $ruta,
            CURLOPT_USERAGENT => 'Consulta Datos',
            CURLOPT_CONNECTTIMEOUT => $conexion,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FAILONERROR => true,
        ]);

        $data = curl_exec($curl);
        $error = curl_error($curl) ?: null;
        curl_close($curl);

        if ($error !== null) {
            return [
                'respuesta' => 'error',
                'titulo' => 'Error',
                'data' => $data,
                'encontrado' => false,
                'mensaje' => 'Error en Api de Búsqueda',
                'errores_curl' => $error,
            ];
        }

        $respuesta = json_decode($data);
        if (!isset($respuesta->respuesta) || $respuesta->respuesta == 'error') {
            return [
                'respuesta' => 'error',
                'titulo' => 'Error',
                'encontrado' => false,
                'data_resp' => $respuesta,
            ];
        }

        return [
            'respuesta' => 'ok',
            'encontrado' => true,
            'api' => true,
            'data' => json_decode($data),
        ];
    }
}
