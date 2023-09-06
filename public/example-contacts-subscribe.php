<?php

declare(strict_types=1);

use ItaliaMultimedia\MagNews\MagNewsContacts;

$projectPath = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR;
require $projectPath . 'vendor/autoload.php';

// edit
$accessToken = '';
// edit
$idDatabase = '';

try {
    $magNewsContacts = new MagNewsContacts($accessToken);
    $mag_data = [
        'EMAIL' => 'magnews@webserv.co',
        'LINGUA' => 'ITA',
        'NAME' => 'Pinco',
        'SURNAME' => 'Pallo',
    ];
    $result = $magNewsContacts->subscribe($mag_data, $idDatabase);
    $log = $magNewsContacts->getLog();
} catch (Throwable $e) {
    $result = false;
    $log = [$e->getMessage()];
}

var_dump($result);
print_r($log);
