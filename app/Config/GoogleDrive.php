<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use Google\Client;
use Google\Service\Drive;

class GoogleDrive extends BaseConfig
{
    // Tu email personal (donde quieres los archivos)
    public $userEmail = 'desarrollo.tecnologico.tarapoto@gmail.com';

    // Ruta al JSON de Service Account
    public $serviceAccountPath = WRITEPATH . 'credentials/service-account.json';

    // ID de carpeta destino en TU Drive (opcional)
    public $folderId = '1W9O_-1RD8hQ1jid_kEZwLP4HLCkFRc0r'; // Cambiar

    // Tamaño máximo de archivo (en bytes)
    public $maxFileSize = 10 * 1024 * 1024; // 10MB

    /**
     * Obtiene el servicio de Drive con impersonation
     */
    public function getDriveService()
    {
        $client = new Client();

        // Configurar Service Account CON impersonation
        $client->setAuthConfig($this->serviceAccountPath);
        $client->addScope(Drive::DRIVE);

        // ¡ESTA LÍNEA ES CLAVE! Usar TU cuota personal
        $client->setSubject($this->userEmail);

        return new Drive($client);
    }

    /**
     * Verifica espacio disponible en TU Drive
     */
    public function getStorageInfo()
    {
        try {
            $service = $this->getDriveService();
            $about = $service->about->get(['fields' => 'storageQuota']);

            return [
                'used' => intval($about->storageQuota->usage),
                'total' => intval($about->storageQuota->limit),
                'free' => intval($about->storageQuota->limit) - intval($about->storageQuota->usage),
                'percent' => round((intval($about->storageQuota->usage) / intval($about->storageQuota->limit)) * 100, 2)
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error al obtener storage info: ' . $e->getMessage());
            return null;
        }
    }
}
