<?php

namespace App\Controllers\Api;

use App\Models\ContribuyenteModel;
use CodeIgniter\RESTful\ResourceController;

use App\Libraries\GoogleDrive;

class GoogleDriveApi extends ResourceController
{
    protected $format = 'json';

    public function getFolder()
    {
        // Obtienes el servicio Drive
        $drive = GoogleDrive::client();

        $folderIdPadre = getenv('FOLDER_GOOGLE_DRIVE');

        $results = $drive->files->listFiles([
            'q' => "'{$folderIdPadre}' in parents and trashed=false",
            'fields' => 'files(id, name, mimeType)',
        ]);

        $carpetas = $results->getFiles();

        return $this->respond($carpetas);
    }

    public function createFolder($nombreCarpeta)
    {
        $drive = GoogleDrive::client();

        $folderIdPadre = getenv('FOLDER_GOOGLE_DRIVE');

        $fileMetadata = new \Google_Service_Drive_DriveFile([
            'name' => $nombreCarpeta,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$folderIdPadre],
        ]);

        $folder = $drive->files->create($fileMetadata, [
            'fields' => 'id',
        ]);

        return $this->respond(['folderId' => $folder->id, 'nameFolder' => $nombreCarpeta]);
    }

    public function uploadFile($folderId, $filePath, $fileName)
    {
        $drive = GoogleDrive::client();

        $fileMetadata = new \Google_Service_Drive_DriveFile([
            'name' => $fileName,
            'parents' => [$folderId],
        ]);

        $content = file_get_contents($filePath);

        $file = $drive->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => mime_content_type($filePath),
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);

        return $this->respond(['fileId' => $file->id, 'fileName' => $fileName]);
    }

    public function deleteFile($fileId)
    {
        $drive = GoogleDrive::client();

        $drive->files->delete($fileId);

        return $this->respond(['message' => 'File deleted successfully']);
    }

    public function getFileMetadata($fileId)
    {
        $drive = GoogleDrive::client();

        $file = $drive->files->get($fileId, [
            'fields' => 'id, name, mimeType, size, createdTime, modifiedTime',
        ]);

        return $this->respond($file);
    }

    public function verifyFolderExists($folderName)
    {
        $drive = GoogleDrive::client();

        $folderIdPadre = getenv('FOLDER_GOOGLE_DRIVE');

        $results = $drive->files->listFiles([
            'q' => "name='{$folderName}' and '{$folderIdPadre}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'fields' => 'files(id, name)',
        ]);

        $folders = $results->getFiles();

        if (count($folders) > 0) {
            return $this->respond(['exists' => true, 'folderId' => $folders[0]->id]);
        } else {
            return $this->respond(['exists' => false]);
        }
    }

    public function listFilesInFolder($folderId)
    {
        $drive = GoogleDrive::client();

        $results = $drive->files->listFiles([
            'q' => "'{$folderId}' in parents and trashed=false",
            'fields' => 'files(id, name, mimeType)',
        ]);

        $files = $results->getFiles();

        return $this->respond($files);
    }
}
