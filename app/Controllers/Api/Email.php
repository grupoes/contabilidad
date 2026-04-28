<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

/**
 * API de Correos Electrónicos
 *
 * Endpoints disponibles:
 *  POST /api/email/send          → Envío genérico (to, subject, body, [cc], [bcc], [attachments])
 *  POST /api/email/send-bulk     → Envío masivo (recipients[], subject, body)
 *  POST /api/email/send-template → Envío con plantilla HTML predefinida
 */
class Email extends ResourceController
{
    protected $format = 'json';

    /** Remitente por defecto (leído desde .env) */
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->fromEmail = env('email.SMTPUser', 'no-reply@grupoesconsultores.com');
        $this->fromName  = 'Grupo ES Consultores';
    }

    // -------------------------------------------------------------------------
    // POST /api/email/send
    // -------------------------------------------------------------------------
    /**
     * Envío de correo genérico.
     *
     * Body JSON:
     * {
     *   "to"      : "dest@ejemplo.com",          // requerido
     *   "subject" : "Asunto del correo",          // requerido
     *   "body"    : "<p>Contenido HTML</p>",      // requerido
     *   "cc"      : "copia@ejemplo.com",          // opcional
     *   "bcc"     : "oculta@ejemplo.com",         // opcional
     *   "from_name": "Nombre remitente"           // opcional
     * }
     */
    public function send()
    {
        try {
            $data = $this->request->getJSON(true);

            // Validación de campos requeridos
            $required = ['to', 'subject', 'body'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->respond([
                        'status'  => false,
                        'message' => "El campo '$field' es requerido.",
                    ], 400);
                }
            }

            if (!filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
                return $this->respond([
                    'status'  => false,
                    'message' => 'El correo destinatario no es válido.',
                ], 422);
            }

            $email = $this->buildEmailService();

            $fromName = $data['from_name'] ?? $this->fromName;
            $email->setFrom($this->fromEmail, $fromName);
            $email->setTo($data['to']);
            $email->setSubject($data['subject']);
            $email->setMessage($data['body']);

            if (!empty($data['cc'])) {
                $email->setCC($data['cc']);
            }

            if (!empty($data['bcc'])) {
                $email->setBCC($data['bcc']);
            }

            if (!$email->send()) {
                return $this->respond([
                    'status'  => false,
                    'message' => 'No se pudo enviar el correo.',
                    'debug'   => $email->printDebugger(['headers']),
                ], 500);
            }

            return $this->respond([
                'status'  => true,
                'message' => 'Correo enviado correctamente.',
                'to'      => $data['to'],
                'subject' => $data['subject'],
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status'  => false,
                'message' => 'Error interno: ' . $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // POST /api/email/send-bulk
    // -------------------------------------------------------------------------
    /**
     * Envío masivo de correos (uno por destinatario).
     *
     * Body JSON:
     * {
     *   "recipients": ["a@ej.com", "b@ej.com"],  // requerido
     *   "subject"   : "Asunto",                   // requerido
     *   "body"      : "<p>Contenido</p>"          // requerido
     * }
     */
    public function sendBulk()
    {
        try {
            $data = $this->request->getJSON(true);

            if (empty($data['recipients']) || !is_array($data['recipients'])) {
                return $this->respond([
                    'status'  => false,
                    'message' => "El campo 'recipients' debe ser un array de correos.",
                ], 400);
            }

            foreach (['subject', 'body'] as $field) {
                if (empty($data[$field])) {
                    return $this->respond([
                        'status'  => false,
                        'message' => "El campo '$field' es requerido.",
                    ], 400);
                }
            }

            $sent   = [];
            $failed = [];

            foreach ($data['recipients'] as $recipient) {
                if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    $failed[] = ['email' => $recipient, 'reason' => 'Correo inválido'];
                    continue;
                }

                $email = $this->buildEmailService();
                $email->setFrom($this->fromEmail, $this->fromName);
                $email->setTo($recipient);
                $email->setSubject($data['subject']);
                $email->setMessage($data['body']);

                if ($email->send()) {
                    $sent[] = $recipient;
                } else {
                    $failed[] = ['email' => $recipient, 'reason' => 'Error de envío'];
                }

                $email->clear();
            }

            return $this->respond([
                'status'       => true,
                'message'      => 'Proceso de envío masivo completado.',
                'total'        => count($data['recipients']),
                'sent_count'   => count($sent),
                'failed_count' => count($failed),
                'sent'         => $sent,
                'failed'       => $failed,
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status'  => false,
                'message' => 'Error interno: ' . $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // POST /api/email/send-template
    // -------------------------------------------------------------------------
    /**
     * Envío con plantilla HTML corporativa predefinida.
     *
     * Body JSON:
     * {
     *   "to"       : "dest@ejemplo.com",      // requerido
     *   "subject"  : "Asunto",                // requerido
     *   "title"    : "Título principal",      // requerido
     *   "content"  : "Párrafo del cuerpo",    // requerido
     *   "button_text": "Ver más",             // opcional
     *   "button_url" : "https://...",         // opcional
     *   "footer_note": "Mensaje de pie"       // opcional
     * }
     */
    public function sendTemplate()
    {
        try {
            $data = $this->request->getJSON(true);

            $required = ['to', 'subject', 'title', 'content'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->respond([
                        'status'  => false,
                        'message' => "El campo '$field' es requerido.",
                    ], 400);
                }
            }

            if (!filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
                return $this->respond([
                    'status'  => false,
                    'message' => 'El correo destinatario no es válido.',
                ], 422);
            }

            $body = $this->buildHtmlTemplate(
                title      : $data['title'],
                content    : $data['content'],
                buttonText : $data['button_text'] ?? null,
                buttonUrl  : $data['button_url']  ?? null,
                footerNote : $data['footer_note'] ?? null,
            );

            $email = $this->buildEmailService();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setTo($data['to']);
            $email->setSubject($data['subject']);
            $email->setMessage($body);

            if (!$email->send()) {
                return $this->respond([
                    'status'  => false,
                    'message' => 'No se pudo enviar el correo.',
                    'debug'   => $email->printDebugger(['headers']),
                ], 500);
            }

            return $this->respond([
                'status'  => true,
                'message' => 'Correo con plantilla enviado correctamente.',
                'to'      => $data['to'],
                'subject' => $data['subject'],
            ]);
        } catch (\Exception $e) {
            return $this->respond([
                'status'  => false,
                'message' => 'Error interno: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    /**
     * Construye y configura el servicio de email de CodeIgniter
     * usando los valores del .env (email.*).
     */
    private function buildEmailService(): \CodeIgniter\Email\Email
    {
        $email = \Config\Services::email();

        $email->initialize([
            'protocol'    => env('email.protocol', 'smtp'),
            'SMTPHost'    => env('email.SMTPHost'),
            'SMTPUser'    => env('email.SMTPUser'),
            'SMTPPass'    => env('email.SMTPPass'),
            'SMTPPort'    => (int) env('email.SMTPPort', 465),
            'SMTPCrypto'  => env('email.SMTPCrypto', 'ssl'),
            'mailType'    => env('email.mailType', 'html'),
            'charset'     => env('email.charset', 'UTF-8'),
            'SMTPTimeout' => (int) env('email.SMTPTimeout', 5),
            'newline'     => "\r\n",
        ]);

        return $email;
    }

    /**
     * Genera el HTML de la plantilla corporativa de correo.
     */
    private function buildHtmlTemplate(
        string  $title,
        string  $content,
        ?string $buttonText = null,
        ?string $buttonUrl  = null,
        ?string $footerNote = null,
    ): string {
        $buttonHtml = '';
        if ($buttonText && $buttonUrl) {
            $buttonHtml = <<<HTML
            <div style="text-align:center;margin:32px 0;">
                <a href="{$buttonUrl}"
                   style="background-color:#1a56db;color:#ffffff;text-decoration:none;
                          padding:14px 32px;border-radius:8px;font-size:15px;
                          font-weight:600;display:inline-block;">
                    {$buttonText}
                </a>
            </div>
            HTML;
        }

        $footerHtml = $footerNote
            ? "<p style='margin:0;color:#6b7280;font-size:12px;'>{$footerNote}</p>"
            : '';

        $year = date('Y');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1.0">
            <title>{$title}</title>
        </head>
        <body style="margin:0;padding:0;background-color:#f3f4f6;font-family:'Segoe UI',Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 0;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0"
                               style="background:#ffffff;border-radius:12px;overflow:hidden;
                                      box-shadow:0 4px 24px rgba(0,0,0,0.07);">
                            <!-- Header -->
                            <tr>
                                <td style="background:linear-gradient(135deg,#1a56db 0%,#1e40af 100%);
                                           padding:36px 40px;text-align:center;">
                                    <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;
                                               letter-spacing:-0.5px;">
                                        Grupo ES Consultores
                                    </h1>
                                    <p style="margin:6px 0 0;color:#bfdbfe;font-size:13px;">
                                        Sistema de Contabilidad
                                    </p>
                                </td>
                            </tr>
                            <!-- Body -->
                            <tr>
                                <td style="padding:40px 48px;">
                                    <h2 style="margin:0 0 16px;color:#111827;font-size:20px;font-weight:700;">
                                        {$title}
                                    </h2>
                                    <div style="color:#374151;font-size:15px;line-height:1.7;">
                                        {$content}
                                    </div>
                                    {$buttonHtml}
                                </td>
                            </tr>
                            <!-- Divider -->
                            <tr>
                                <td style="padding:0 48px;">
                                    <hr style="border:none;border-top:1px solid #e5e7eb;margin:0;">
                                </td>
                            </tr>
                            <!-- Footer -->
                            <tr>
                                <td style="padding:24px 48px;background:#f9fafb;text-align:center;">
                                    {$footerHtml}
                                    <p style="margin:8px 0 0;color:#9ca3af;font-size:12px;">
                                        &copy; {$year} Grupo ES Consultores &mdash; Todos los derechos reservados.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        HTML;
    }
}
