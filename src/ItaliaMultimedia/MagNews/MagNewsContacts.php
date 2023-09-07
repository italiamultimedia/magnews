<?php

declare(strict_types=1);

namespace ItaliaMultimedia\MagNews;

use ItaliaMultimedia\MagNews\DataTransfer\MagNewsResponse;

use function print_r;

final class MagNewsContacts extends AbstractMagNews
{
    /**
     * @param array<string,string> $userData
     */
    public function subscribe(array $userData, string $idDatabase): MagNewsResponse
    {
        $postData = [
            'options' => ['iddatabase' => $idDatabase],
            'values' => $userData,
        ];

        $this->log[] = __METHOD__ . 'POST DATA: ' . print_r($postData, true);

        $data = $this->getApiData("/contacts/subscribe", true, $postData);

        $this->log[] = __METHOD__ . ' ' . print_r($data, true);

        return $data;
    }
}
