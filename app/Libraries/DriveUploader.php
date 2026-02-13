<?php

namespace App\Libraries;

use Config\GoogleDrive;
use Google\Service\Drive\DriveFile;
use Google\Http\MediaFileUpload;

class DriveUploader
{
    protected $service;
    protected $config;

    public function __construct()
    {
        $this->config = new GoogleDrive();
        $this->service = $this->config->getDriveService();
    }

    /**
     * Sube un archivo a TU Google Drive personal
     */
    public function uploadFile($filePath, $fileName = null, $folderId = null)
    {
        try {
            // Verificar si el archivo existe
            if (!file_exists($filePath)) {
                throw new \Exception("Archivo no encontrado: {$filePath}");
            }

            // Usar nombre original si no se especifica
            if (!$fileName) {
                $fileName = basename($filePath);
            }

            // Usar carpeta por defecto si no se especifica
            if (!$folderId) {
                $folderId = $this->config->folderId;
            }

            // Metadata del archivo
            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => $folderId ? [$folderId] : []
            ]);

            // Configurar upload
            $mimeType = mime_content_type($filePath);
            $fileSize = filesize($filePath);

            $media = new MediaFileUpload(
                $this->service->getClient(),
                $this->service,
                'files',
                $fileMetadata,
                $mimeType,
                true // resumable para archivos grandes
            );

            $media->setFileSize($fileSize);

            // Leer y subir el archivo en chunks
            $handle = fopen($filePath, "rb");
            $status = false;

            while (!$status && !feof($handle)) {
                $chunk = fread($handle, 1024 * 1024); // 1MB chunks
                $status = $media->nextChunk($chunk);
            }

            fclose($handle);

            // Obtener enlace de vista web
            if ($status) {
                $fileId = $status->id;
                $webViewLink = "https://drive.google.com/file/d/{$fileId}/view";

                return [
                    'success' => true,
                    'file_id' => $fileId,
                    'file_name' => $status->name,
                    'web_view_link' => $webViewLink,
                    'mime_type' => $status->mimeType,
                    'size' => $status->size,
                    'created_time' => $status->createdTime
                ];
            }

            throw new \Exception("Error en la subida del archivo");
        } catch (\Google\Service\Exception $e) {
            // Manejar errores específicos de Google
            $error = json_decode($e->getMessage(), true);
            $errorMsg = $error['error']['message'] ?? $e->getMessage();

            log_message('error', 'Error Google Drive: ' . $errorMsg);

            // Si es error de quota, sugerir solución
            if (
                strpos($errorMsg, 'quota') !== false ||
                strpos($errorMsg, 'rateLimit') !== false
            ) {
                $errorMsg .= ' - ¿Estás usando withSubject() correctamente?';
            }

            throw new \Exception($errorMsg);
        } catch (\Exception $e) {
            log_message('error', 'Error en DriveUploader: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sube archivo desde formulario HTTP
     */
    public function uploadFromForm($uploadedFile, $customName = null)
    {
        // Validar archivo subido
        if (!$uploadedFile->isValid()) {
            throw new \Exception($uploadedFile->getErrorString());
        }

        // Validar tamaño
        if ($uploadedFile->getSize() > $this->config->maxFileSize) {
            throw new \Exception("Archivo demasiado grande. Máximo: " .
                ($this->config->maxFileSize / 1024 / 1024) . " MB");
        }

        // Mover a temporal
        $tempPath = WRITEPATH . 'uploads/' . $uploadedFile->getRandomName();
        $uploadedFile->move(WRITEPATH . 'uploads/', $uploadedFile->getRandomName());

        try {
            // Subir a Drive
            $result = $this->uploadFile(
                $tempPath,
                $customName ?: $uploadedFile->getClientName()
            );

            // Eliminar archivo temporal
            unlink($tempPath);

            return $result;
        } catch (\Exception $e) {
            // Limpiar en caso de error
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            throw $e;
        }
    }

    /**
     * Lista archivos en la carpeta
     */
    public function listFiles($folderId = null, $pageSize = 50)
    {
        try {
            $folderId = $folderId ?: $this->config->folderId;

            $query = "'{$folderId}' in parents";
            $optParams = [
                'pageSize' => $pageSize,
                'fields' => 'files(id, name, size, mimeType, webViewLink, createdTime)',
                'q' => $query,
                'orderBy' => 'createdTime desc'
            ];

            $results = $this->service->files->listFiles($optParams);

            return $results->getFiles();
        } catch (\Exception $e) {
            log_message('error', 'Error listando archivos: ' . $e->getMessage());
            return [];
        }
    }
}
