<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\DriveUploader;
use Config\Services;

class DriveController extends BaseController
{
    protected $uploader;
    protected $validation;

    public function __construct()
    {
        $this->uploader = new DriveUploader();
        $this->validation = Services::validation();
    }

    /**
     * Muestra formulario de upload
     */
    public function index()
    {
        // Verificar espacio disponible
        $driveConfig = new \Config\GoogleDrive();
        $storageInfo = $driveConfig->getStorageInfo();

        return view('upload_form', [
            'storageInfo' => $storageInfo
        ]);
    }

    /**
     * Procesa el upload del archivo
     */
    public function upload()
    {
        // Validar
        $rules = [
            'file' => 'uploaded[file]|max_size[file,10240]', // 10MB max
            'user_email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $file = $this->request->getFile('file');
            $userEmail = $this->request->getPost('user_email');

            // Crear nombre personalizado
            $timestamp = date('Ymd_His');
            $customName = "{$userEmail}_{$timestamp}_{$file->getClientName()}";

            // Subir a Drive
            $result = $this->uploader->uploadFromForm($file, $customName);

            // Guardar registro en base de datos (opcional)
            $this->saveUploadRecord($result, $userEmail);

            return redirect()->to('/drive/success')
                ->with('success', 'Archivo subido exitosamente')
                ->with('fileInfo', $result);
        } catch (\Exception $e) {
            log_message('error', 'Upload error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al subir archivo: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint para upload (para AJAX)
     */
    public function apiUpload()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)
                ->setJSON(['error' => 'Método no permitido']);
        }

        try {
            $file = $this->request->getFile('file');
            $userEmail = $this->request->getPost('user_email');

            if (!$file || !$file->isValid()) {
                throw new \Exception('Archivo inválido');
            }

            $timestamp = date('Ymd_His');
            $customName = "{$userEmail}_{$timestamp}_{$file->getClientName()}";

            $result = $this->uploader->uploadFromForm($file, $customName);

            return $this->response->setJSON([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
        }
    }

    /**
     * Lista archivos subidos
     */
    public function listFiles()
    {
        $files = $this->uploader->listFiles();

        return view('files_list', [
            'files' => $files
        ]);
    }

    /**
     * Verifica estado del servicio
     */
    public function checkStatus()
    {
        try {
            $storageInfo = (new \Config\GoogleDrive())->getStorageInfo();

            return $this->response->setJSON([
                'status' => 'ok',
                'storage' => $storageInfo,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Guarda registro en base de datos (opcional)
     */
    private function saveUploadRecord($fileInfo, $userEmail)
    {
        // Si tienes modelo para guardar registros
        // $model = new \App\Models\UploadModel();
        // $model->save([
        //     'file_id' => $fileInfo['file_id'],
        //     'file_name' => $fileInfo['file_name'],
        //     'user_email' => $userEmail,
        //     'uploaded_at' => date('Y-m-d H:i:s')
        // ]);

        // Por ahora solo log
        log_message('info', "Archivo subido por {$userEmail}: {$fileInfo['file_name']}");
    }
}
