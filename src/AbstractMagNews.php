<?php
namespace Project;

abstract class AbstractMagNews
{
    const API_URL = 'https://ws-mn1.mag-news.it/ws/rest/api/v19';
    protected $accessToken;
    protected $debug;
    protected $debugId;
    protected $log;

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
        $this->debug = true;
        $this->log = [];
    }

    protected function getApiData($resource, $isPost = false, $postData = [])
    {
        $time = [
            'start' => microtime(true),
            'end' => null
        ];

        if ($this->debug) {
            $this->debugId = uniqid();
        }

        if ($isPost) {
            $json = self::getCurlData(self::API_URL . $resource, true, $postData);
        } else {
            $json = self::getCurlData(self::API_URL . $resource);
        }

        $data = json_decode($json, true);

        $time['end'] = microtime(true);

        return $data ?: false;
    }

    protected function getCurlData($url, $isPost = false, $postData = [])
    {
        /*
         * Netcat simple proxy
         * nc -v -l localhost 12345
         */
        // $url = 'http://localhost:12345';
        $out = null;
        if ($this->debug) {
            ob_start();
            $out = fopen('php://output', 'w');
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
        ));

        if ($this->debug) {
            // curl_setopt($curl, CURLINFO_HEADER_OUT, 1); /* verbose not working if this is enabled */
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_STDERR, $out);
        }

        if ($isPost) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

            if (!empty($postData)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
            }
        }

        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken
            ]
        );

        if ($resp = curl_exec($curl)) {
            $curl_info = null;
            if ($this->debug) {
                $curl_info = curl_getinfo($curl);
            }

            curl_close($curl);

            if ($this->debug) {
                fclose($out);
                $curl_debug = ob_get_clean();

                $this->log[] = ['curl info' => $curl_info];
                $this->log[] = ['curl verbose' => $curl_debug];
                $this->log[] = ['curl response' => $resp];
            }

            list ($header, $body) = explode("\r\n\r\n", $resp, 2);
            $body = trim($body);
            if (empty($body)) {
                throw new MagNewsException(sprintf('Empty body. header: %s', $header));
            }
            return $body;
        } else {
            throw new MagNewsException(curl_error($curl), curl_errno($curl));
        }
    }

    public function getLog($asString = false)
    {
        return $asString ? implode(PHP_EOL, $this->log) . PHP_EOL : $this->log;
    }
}
