<?php
namespace Project;

class MagNewsContacts extends AbstractMagNews
{
    public function subscribe($userData = [], $idDatabase = null)
    {
        $post_data = [
            'options' => ['iddatabase' => $idDatabase],
            'values' => $userData,
        ];

        $this->log[] = __METHOD__  . 'POST DATA: ' . print_r($post_data, true);

        $data = $this->getApiData("/contacts/subscribe", true, $post_data);

        $this->log[] = __METHOD__  . ' ' . print_r($data, true);

        return !empty($data['ok']) ? true: false;
    }
}
