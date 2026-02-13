<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Google\Client;
use Google\Service\Drive;

class TestDrive extends BaseController
{
    public function index()
    {
        echo "<h2>Diagnóstico completo Google Drive API</h2>";

        // Ruta al JSON (ajusta según tu estructura)
        $serviceAccountPath = WRITEPATH . 'credentials/service-account.json';

        if (!file_exists($serviceAccountPath)) {
            die("❌ Archivo service-account.json no encontrado en: " . $serviceAccountPath);
        }

        echo "✅ Archivo encontrado: " . $serviceAccountPath . "<br>";

        // Leer configuración
        $saConfig = json_decode(file_get_contents($serviceAccountPath), true);
        echo "📧 Service Account: " . $saConfig['client_email'] . "<br>";
        echo "🔑 Client ID: " . $saConfig['client_id'] . "<br>";

        // ========== PRUEBA 1: Sin impersonation ==========
        echo "<h3>1. Probando SIN impersonation:</h3>";

        try {
            $client1 = new Client();
            $client1->setAuthConfig($serviceAccountPath);
            $client1->addScope(Drive::DRIVE_FILE); // Scope más básico

            $service1 = new Drive($client1);
            $about1 = $service1->about->get(['fields' => 'user']);

            echo "✅ Conectado como: " . $about1->user->emailAddress . "<br>";
            echo "ℹ️  Usando scope: drive.file (solo archivos creados por la app)<br>";
        } catch (\Exception $e) {
            echo "❌ Error sin impersonation: " . $this->parseError($e) . "<br>";
        }

        // ========== PRUEBA 2: Con impersonation (TU email) ==========
        echo "<h3>2. Probando CON impersonation:</h3>";

        // Tu email personal (debe tener acceso a Drive)
        $userEmail = 'desarrollo.tecnologico.tarapoto@gmail.com'; // ¡CAMBIAR ESTO!

        try {
            $client2 = new Client();
            $client2->setAuthConfig($serviceAccountPath);
            $client2->addScope(Drive::DRIVE); // Scope completo

            // Intentar impersonation
            $client2->setSubject($userEmail);

            $service2 = new Drive($client2);
            $about2 = $service2->about->get(['fields' => 'user,storageQuota']);

            echo "✅ Conectado como: " . $about2->user->emailAddress . "<br>";

            if ($about2->user->emailAddress === $userEmail) {
                echo "🎉 ¡Impersonation exitosa!<br>";
            }

            // Mostrar espacio
            echo "💾 Espacio usado: " .
                number_format($about2->storageQuota->usage / 1024 / 1024 / 1024, 2) . " GB<br>";
            echo "💾 Espacio total: " .
                number_format($about2->storageQuota->limit / 1024 / 1024 / 1024, 2) . " GB<br>";

            // ========== PRUEBA 3: Subir archivo pequeño ==========
            echo "<h3>3. Probando upload pequeño:</h3>";

            $testContent = "Prueba de upload " . date('Y-m-d H:i:s');
            $tempFile = WRITEPATH . 'test_upload_' . time() . '.txt';
            file_put_contents($tempFile, $testContent);

            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => 'test_' . date('Ymd_His') . '.txt',
            ]);

            $file = $service2->files->create($fileMetadata, [
                'data' => $testContent,
                'mimeType' => 'text/plain',
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink'
            ]);

            echo "✅ Upload exitoso!<br>";
            echo "📄 ID: " . $file->id . "<br>";
            echo "📄 Nombre: " . $file->name . "<br>";
            echo "🔗 Enlace: <a href='" . $file->webViewLink . "' target='_blank'>Ver en Drive</a><br>";

            // Limpiar
            unlink($tempFile);
        } catch (\Exception $e) {
            echo "❌ Error con impersonation: " . $this->parseError($e) . "<br>";

            // Mostrar solución específica
            $this->showSolution($e, $userEmail, $saConfig['client_email']);
        }

        // ========== PRUEBA 4: Con scope alternativo ==========
        echo "<h3>4. Probando con scope drive.file:</h3>";

        try {
            $client3 = new Client();
            $client3->setAuthConfig($serviceAccountPath);
            $client3->addScope(Drive::DRIVE_FILE); // Scope más restrictivo
            $client3->setSubject($userEmail);

            $service3 = new Drive($client3);

            // Intentar crear archivo
            $testContent2 = "Prueba con scope drive.file " . date('H:i:s');
            $fileMetadata2 = new \Google\Service\Drive\DriveFile([
                'name' => 'test_drive_file_' . time() . '.txt',
            ]);

            $file2 = $service3->files->create($fileMetadata2, [
                'data' => $testContent2,
                'mimeType' => 'text/plain',
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);

            echo "✅ Upload con drive.file exitoso!<br>";
            echo "📄 ID: " . $file2->id . "<br>";
        } catch (\Exception $e) {
            echo "❌ Error con drive.file: " . $this->parseError($e) . "<br>";
        }
    }

    /**
     * Parsear error de Google
     */
    private function parseError($e)
    {
        $message = $e->getMessage();

        // Intentar decodificar JSON error
        if (strpos($message, '{') !== false) {
            $start = strpos($message, '{');
            $end = strrpos($message, '}') + 1;
            $jsonStr = substr($message, $start, $end - $start);

            $errorData = json_decode($jsonStr, true);
            if ($errorData) {
                return $errorData['error'] . ": " . ($errorData['error_description'] ?? '');
            }
        }

        return $message;
    }

    /**
     * Mostrar solución específica al error
     */
    private function showSolution($e, $userEmail, $serviceAccountEmail)
    {
        $error = $this->parseError($e);

        echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 10px 0;'>";
        echo "<h4>🛠️ SOLUCIÓN PARA: <code>$error</code></h4>";

        if (strpos($error, 'unauthorized_client') !== false) {
            echo "
            <p><strong>Problema:</strong> La Service Account no tiene permisos para usar OAuth 2.0</p>
            
            <p><strong>Solución paso a paso:</strong></p>
            <ol>
                <li><strong>Habilitar Domain-wide Delegation:</strong>
                    <ul>
                        <li>Ve a <a href='https://console.cloud.google.com/iam-admin/serviceaccounts' target='_blank'>Service Accounts</a></li>
                        <li>Encuentra: <code>$serviceAccountEmail</code></li>
                        <li>Haz clic en ⋮ → <strong>Edit</strong></li>
                        <li>Marca <strong>✔ Enable G Suite Domain-wide Delegation</strong></li>
                        <li>Guarda</li>
                    </ul>
                </li>
                
                <li><strong>Configurar OAuth Scopes en Admin Console:</strong>
                    <ul>
                        <li>Ve a <a href='https://admin.google.com' target='_blank'>Google Admin Console</a></li>
                        <li>Security → API controls → Manage Domain-wide Delegation</li>
                        <li>Click <strong>Add new</strong></li>
                        <li>Client ID: <code>" . json_decode(file_get_contents(WRITEPATH . 'credentials/service-account.json'), true)['client_id'] . "</code></li>
                        <li>OAuth Scopes (copiar y pegar):
                            <pre style='background: white; padding: 10px;'>
https://www.googleapis.com/auth/drive
https://www.googleapis.com/auth/drive.file
https://www.googleapis.com/auth/drive.appdata</pre>
                        </li>
                        <li>Click <strong>Authorize</strong></li>
                    </ul>
                </li>
                
                <li><strong>Compartir tu Drive con la Service Account:</strong>
                    <ul>
                        <li>Abre <a href='https://drive.google.com' target='_blank'>Google Drive</a></li>
                        <li>Crea o selecciona una carpeta</li>
                        <li>Click derecho → <strong>Compartir</strong></li>
                        <li>Añadir: <code>$serviceAccountEmail</code></li>
                        <li>Permisos: <strong>Editor</strong></li>
                    </ul>
                </li>
                
                <li><strong>Esperar 5-10 minutos</strong> para que los cambios propaguen</li>
                
                <li><strong>Probar de nuevo</strong></li>
            </ol>
            ";
        } elseif (strpos($error, 'access_denied') !== false) {
            echo "
            <p><strong>Problema:</strong> No tienes permisos para impersonar a <code>$userEmail</code></p>
            
            <p><strong>Solución:</strong></p>
            <ul>
                <li>Verifica que <code>$userEmail</code> existe y tiene Google Drive</li>
                <li>En Admin Console, asegúrate que el usuario tiene permisos</li>
                <li>Si usas cuenta personal (gmail.com), necesitas usar OAuth 2.0 normal en lugar de Service Account</li>
            </ul>
            ";
        }

        echo "</div>";
    }

    /**
     * Método alternativo: Usar OAuth 2.0 normal (sin Service Account)
     */
    public function oauthTest()
    {
        echo "<h2>Prueba con OAuth 2.0 normal (recomendado para cuentas personales)</h2>";

        // Solo para desarrollo local
        $redirectUri = 'http://localhost:8000/drive/oauth-callback';

        $client = new Client();
        $client->setApplicationName('Drive Upload App');
        $client->setScopes(Drive::DRIVE_FILE);
        $client->setAuthConfig(WRITEPATH . 'credentials/oauth-credentials.json'); // Diferente archivo
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->setRedirectUri($redirectUri);

        // Generar URL de autorización
        $authUrl = $client->createAuthUrl();

        echo "🔗 <a href='$authUrl' target='_blank'>Autorizar aplicación</a><br>";
        echo "Después de autorizar, copia el código y pégalo aquí:<br>";

        echo "<form method='post'>
            <input type='text' name='code' placeholder='Código de autorización' style='width: 500px;'>
            <button type='submit'>Continuar</button>
        </form>";

        if ($this->request->getPost('code')) {
            try {
                $accessToken = $client->fetchAccessTokenWithAuthCode($this->request->getPost('code'));
                $client->setAccessToken($accessToken);

                // Guardar token para uso futuro
                file_put_contents(WRITEPATH . 'credentials/token.json', json_encode($accessToken));

                echo "✅ Token obtenido exitosamente!<br>";

                // Probar conexión
                $service = new Drive($client);
                $about = $service->about->get(['fields' => 'user']);
                echo "Conectado como: " . $about->user->emailAddress;
            } catch (\Exception $e) {
                echo "❌ Error: " . $e->getMessage();
            }
        }
    }
}
