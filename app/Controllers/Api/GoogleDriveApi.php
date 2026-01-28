<?php

namespace App\Controllers\Api;

use App\Models\ContribuyenteModel;
use CodeIgniter\RESTful\ResourceController;

use App\Libraries\GoogleDrive;
use App\Models\FoldersFilesModel;

class GoogleDriveApi extends ResourceController
{
    protected $format = 'json';

    public function getFolder()
    {
        // Obtienes el servicio Drive
        $drive = GoogleDrive::client();

        $datos = $this->request->getJSON(true);
        $ruc = $datos['ruc'];

        $contrib = new ContribuyenteModel();

        $parentFolderId = $datos['folderParentId'];

        if ($parentFolderId == 0) {
            $contribuyente = $contrib->select('folderParentId')->where('ruc', $ruc)->first();
            $parentFolderId = $contribuyente['folderParentId'];
        }

        $results = $drive->files->listFiles([
            'q' => "'{$parentFolderId}' in parents and trashed=false",
            'fields' => 'files(id, name, mimeType)',
        ]);

        $carpetas = $results->getFiles();

        return $this->respond([
            'status' => 'success',
            'folders' => $carpetas,
        ]);
    }

    public function createFolder($nombreCarpeta, $parentFolderId = 0)
    {
        $drive = GoogleDrive::client();

        if ($parentFolderId == 0) {
            $folderIdPadre  = getenv('FOLDER_GOOGLE_DRIVE');
        } else {
            $folderIdPadre = $parentFolderId;
        }

        $fileMetadata = new \Google_Service_Drive_DriveFile([
            'name' => $nombreCarpeta,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$folderIdPadre],
        ]);

        $folder = $drive->files->create($fileMetadata, [
            'fields' => 'id',
        ]);

        return ['folderId' => $folder->id, 'nameFolder' => $nombreCarpeta];
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

    public function verifyFolderExists($folderName, $parentFolderId = 0)
    {
        $drive = GoogleDrive::client();

        $folderIdPadre = getenv('FOLDER_GOOGLE_DRIVE');

        if ($parentFolderId != 0) {
            $folderIdPadre = $parentFolderId;
        }

        $results = $drive->files->listFiles([
            'q' => "name='{$folderName}' and '{$folderIdPadre}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'fields' => 'files(id, name)',
        ]);

        $folders = $results->getFiles();

        if (count($folders) > 0) {
            return ['exists' => true, 'folderId' => $folders[0]->id];
        } else {
            return ['exists' => false];
        }
    }

    public function listFilesInFolder($folderId)
    {
        try {
            $drive = GoogleDrive::client();

            $results = $drive->files->listFiles([
                'q' => "'{$folderId}' in parents and trashed=false",
                'fields' => 'files(id, name, mimeType, webViewLink)',
            ]);

            $files = $results->getFiles();

            return $this->respond([
                'status' => 'success',
                'foldersFiles' => $files,
                'message' => 'Archivos recuperados exitosamente',
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Error retrieving files: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function apiVerifyFolderExists()
    {
        $data = $this->request->getJSON(true);
        $year = $data['year'];
        $ruc = $data['ruc'];

        $cont = new ContribuyenteModel();
        $folders = new FoldersFilesModel();

        try {
            $contribuyente = $cont->select('id, ruc, razon_social, folderParentId')->where('ruc', $ruc)->first();

            $folderName = $ruc . '-' . $contribuyente['razon_social'];

            if ($contribuyente['folderParentId'] == "") {
                $createResult = $this->createFolder($folderName, 0);
                $folderId = $createResult['folderId'];

                $cont->update($contribuyente['id'], ['folderParentId' => $folderId]);

                $folders->insert([
                    'idfolderfile' => $folderId,
                    'name' => $folderName,
                    'tipo' => 'folder',
                    'parentId' => 0,
                    'estado' => 1,
                ]);

                $createYearFolder = $this->createFolder($year, $folderId);
                $yearFolderId = $createYearFolder['folderId'];

                $folders->insert([
                    'idfolderfile' => $yearFolderId,
                    'name' => $year,
                    'tipo' => 'folder',
                    'parentId' => $folderId,
                    'estado' => 1,
                ]);

                return $this->respond(['status' => 'success', 'message' => 'Folder and subfolders created successfully']);
            } else {
                $resultYear = $this->verifyFolderExists($year, $contribuyente['folderParentId']);

                if (!$resultYear['exists']) {
                    $createYearFolder = $this->createFolder($year, $contribuyente['folderParentId']);
                    $yearFolderId = $createYearFolder['folderId'];

                    $folders->insert([
                        'idfolderfile' => $yearFolderId,
                        'name' => $year,
                        'tipo' => 'folder',
                        'parentId' => $contribuyente['folderParentId'],
                        'estado' => 1,
                    ]);
                }

                return $this->respond(['status' => 'success', 'message' => 'Folder and subfolders already exist']);
            }
        } catch (\Exception $e) {
            return $this->respond(['status' => 'error', 'message' => 'Error retrieving contributor data: ' . $e->getMessage()], 500);
        }
    }

    public function folderMonts($folderId)
    {
        $meses = [
            '01' => 'ENERO',
            '02' => 'FEBRERO',
            '03' => 'MARZO',
            '04' => 'ABRIL',
            '05' => 'MAYO',
            '06' => 'JUNIO',
            '07' => 'JULIO',
            '08' => 'AGOSTO',
            '09' => 'SETIEMBRE',
            '10' => 'OCTUBRE',
            '11' => 'NOVIEMBRE',
            '12' => 'DICIEMBRE',
        ];

        $month = date('m');

        $mes = $meses[$month];

        try {

            $verifyMonth = $this->verifyFolderExists($mes, $folderId);

            if (!$verifyMonth['exists']) {
                $createMonthFolder = $this->createFolder($mes, $folderId);
                $monthFolderId = $createMonthFolder['folderId'];

                $foldersFilesModel = new FoldersFilesModel();
                $foldersFilesModel->insert([
                    'idfolderfile' => $monthFolderId,
                    'name' => $mes,
                    'tipo' => 'folder',
                    'parentId' => $folderId,
                    'estado' => 1,
                ]);

                return $this->respond([
                    'status' => 'success',
                    'message' => 'carpeta del mes creada',
                ]);
            } else {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'ya existe la carpeta del mes',
                ]);
            }
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Error al verificar o crear la carpeta del mes: ' . $e->getMessage(),
            ]);
        }
    }

    public function apiCreateFolder()
    {
        try {
            $datos = $this->request->getJSON(true);

            $nombreCarpeta = $datos['folderName'];
            $parentFolderId = $datos['parentFolderId'];

            $drive = GoogleDrive::client();

            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => $nombreCarpeta,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentFolderId],
            ]);

            $folder = $drive->files->create($fileMetadata, [
                'fields' => 'id',
            ]);

            return $this->respond([
                'status' => 'success',
                'message' => 'Carpeta creada exitosamente',
                'folderId' => $folder->id,
                'nameFolder' => $nombreCarpeta
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Error al crear la carpeta: ' . $e->getMessage(),
            ]);
        }
    }
}
