<?php

declare(strict_types=1);

use ItaliaMultimedia\MagNews\MagNewsContacts;

$projectPath = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR;
require $projectPath . 'vendor/autoload.php';

/** Edit start */
$accessToken = '';
$idDatabase = '';
/** Edit stop */

// Initialize.
$magNewsContacts = new MagNewsContacts($accessToken);

$response = null;
try {
    /**
     * Request data.
     * @phpcs:disable SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder
     */
    $mag_data = [
        'EMAIL' => 'magnews@webserv.co',
        'LINGUA' => 'ITA',
        'NOME' => 'Pinco',
        'COGNOME' => 'Pallo',
    ];
    // @phpcs:enable

    /**
     * Get response
     * [MagNewsResponse](https://github.com/italiamultimedia/magnews/blob/main/src/ItaliaMultimedia/MagNews/DataTransfer/MagNewsResponse.php)
     *
     * Check $response->ok for result
     */
    $response = $magNewsContacts->subscribe($mag_data, $idDatabase);
} catch (Throwable $e) {
    echo $e->getMessage();
}

// Debug
echo '<pre>';
echo 'Response:';
var_dump($response);
echo '<hr>';
echo 'Log:';
var_dump($magNewsContacts->getLog());
echo '</pre>';
exit;
