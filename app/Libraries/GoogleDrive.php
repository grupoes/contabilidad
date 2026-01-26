<?php

namespace App\Libraries;

use Google\Client;
use Google\Service\Drive;

class GoogleDrive
{
    public static function client(): Drive
    {
        $client = new Client();
        $client->setAuthConfig(APPPATH . 'Config/google/service-account.json');
        $client->addScope(Drive::DRIVE);

        return new Drive($client);
    }
}
