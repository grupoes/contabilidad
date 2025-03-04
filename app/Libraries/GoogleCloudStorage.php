<?php

namespace App\Libraries;

use Google\Cloud\Storage\StorageClient;

class GoogleCloudStorage
{
    protected $storage;
    protected $bucket;

    public function __construct()
    {
        // Obtener valores del .env
        $credentialsPath = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        $bucketName = getenv('GOOGLE_CLOUD_BUCKET');

        // Configurar cliente de Google Cloud Storage
        $this->storage = new StorageClient([
            'keyFilePath' => WRITEPATH .$credentialsPath
        ]);

        $this->bucket = $this->storage->bucket($bucketName);
    }

    /**
     * Sube un archivo desde una URL a Google Cloud Storage.
     *
     * @param string $fileUrl URL del archivo a descargar
     * @param string $destFileName Nombre de destino en el bucket
     * @return string URL pública del archivo en Google Cloud Storage
     */
    public function uploadFromUrl($fileUrl, $destFileName)
    {
        // Descargar el archivo temporalmente
        $tempPath = WRITEPATH . 'uploads/' . basename($fileUrl);
        file_put_contents($tempPath, file_get_contents($fileUrl));

        // Subir archivo a Google Cloud Storage
        $file = fopen($tempPath, 'r');
        $object = $this->bucket->upload($file, ['name' => $destFileName]);

        // Hacer público el archivo (opcional)
        $object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);

        // Eliminar archivo local
        unlink($tempPath);

        return "https://storage.googleapis.com/" . getenv('GOOGLE_CLOUD_BUCKET') . "/$destFileName";
    }

    public function uploadFromContent($fileContent, $destFileName)
    {
        $storage = new StorageClient([
            'keyFilePath' => WRITEPATH . 'keys.json' // Ruta de credenciales
        ]);

        $bucket = $storage->bucket('appwhatsapp');

        // Subir archivo
        $object = $bucket->upload($fileContent, [
            'name' => $destFileName
        ]);

        return "https://storage.googleapis.com/appwhatsapp/" . $destFileName;
    }
}
