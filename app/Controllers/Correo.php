<?php

namespace App\Controllers;

class Correo extends BaseController
{
    public function enviar()
    {
        $email = \Config\Services::email();

        $email->setFrom('contabilidad@grupoesconsultores.com', 'GRUPO ES CONSULTORES S.A.C.');
        $email->setTo('desarrollo.tecnologico.tarapoto@gmail.com');

        $email->setSubject('Correo de prueba desde CodeIgniter 4 + cPanel SMTP');
        $email->setMessage('<h3>Este es un correo enviado con SMTP desde tu VPS y cPanel.</h3>');

        if ($email->send()) {
            echo '✅ Correo enviado correctamente.';
        } else {
            // Mostrar errores si falla
            echo '❌ Error al enviar el correo.<br>';
            print_r($email->printDebugger(['headers']));
        }
    }
}
