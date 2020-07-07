<?php
require __DIR__ . '/../vendor/autoload.php';

$accessToken = ''; // edit
$idDatabase = ''; // edit

try {
    $magNewsContacts = new \Project\MagNewsContacts($accessToken);
    $mag_data = [
        'EMAIL' => 'magnews@webserv.co',
        'NAME' => 'Pinco',
        'SURNAME' => 'Pallo',
        'LINGUA' => 'ITA',
    ];
    $result = $magNewsContacts->subscribe($mag_data, $idDatabase);
    $log = $magNewsContacts->getLog();
} catch (\Project\MagNewsException $e) {
    $log = [$e->getMessage()];
}

print_r($log);
