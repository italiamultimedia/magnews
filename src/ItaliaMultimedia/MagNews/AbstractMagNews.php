<?php

declare(strict_types=1);

namespace ItaliaMultimedia\MagNews;

use CurlHandle;
use ItaliaMultimedia\MagNews\DataTransfer\MagNewsResponse;
use OutOfBoundsException;
use UnexpectedValueException;

use function array_key_exists;
use function curl_close;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function curl_setopt_array;
use function explode;
use function fclose;
use function fopen;
use function is_array;
use function is_bool;
use function is_int;
use function is_resource;
use function is_string;
use function json_decode;
use function json_encode;
use function ob_get_clean;
use function ob_start;
use function sprintf;
use function trim;

use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_HEADER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_SSL_VERIFYPEER;
use const CURLOPT_STDERR;
use const CURLOPT_URL;
use const CURLOPT_VERBOSE;

abstract class AbstractMagNews
{
    private const API_URL = 'https://ws-mn1.mag-news.it/ws/rest/api/v19';

    private const RESPONSE_FIELDS = [
        'ok',
        'pk',
        'idcontact',
        'action',
        'errors',
        'sendemail',
        'enterworkflow',
        'idwebtracking',
    ];

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @var array<int,mixed> $log
     * @phpcs: enable
     */
    protected array $log;

    private bool $debug;

    public function __construct(private string $accessToken)
    {
        $this->debug = true;
        $this->log = [];
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @return array<int,mixed>
     * @phpcs: enable
     */
    public function getLog(): array
    {
        return $this->log;
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<string,array<string,string>|string> $postData
     * @phpcs: enable
     */
    protected function getApiData(string $resource, bool $isPost, array $postData): MagNewsResponse
    {
        /*
        $time = [
            'end' => null,
            'start' => microtime(true),
        ];
        */

        /*
        if ($this->debug) {
            $this->debugId = uniqid();
        }
        */

        $json = $isPost
            ? self::getCurlData(self::API_URL . $resource, true, $postData)
            : self::getCurlData(self::API_URL . $resource, false, []);

        $data = json_decode($json, true);

        //$time['end'] = microtime(true);

        if (!is_array($data)) {
            throw new UnexpectedValueException('Data is not an array.');
        }

        return $this->hydrateResponse($data);
    }

    /**
     * @param array<string,array<string,string>|string> $postData
     * @todo fix complexity
     * @phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
     * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
     * @phpcs:disable SlevomatCodingStandard.Files.FunctionLength.FunctionLength
     */
    private function getCurlData(string $url, bool $isPost, array $postData): string
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
            if (!is_resource($out)) {
                throw new UnexpectedValueException('Not a resource.');
            }
        }

        $curl = curl_init();

        if (!$curl instanceof CurlHandle) {
            throw new UnexpectedValueException('Not a CurlHandle.');
        }

        curl_setopt_array($curl, array(
            CURLOPT_HEADER => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_URL => $url,
        ));

        if ($this->debug) {
            // curl_setopt($curl, CURLINFO_HEADER_OUT, 1); /* verbose not working if this is enabled */
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
            curl_setopt($curl, CURLOPT_STDERR, $out);
        }

        if ($isPost) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

            if ($postData !== []) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
            }
        }

        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken,
            ],
        );

        $resp = curl_exec($curl);

        if ($resp === false) {
            throw new UnexpectedValueException(curl_error($curl), curl_errno($curl));
        }

        $curlInfo = null;
        if ($this->debug) {
            $curlInfo = curl_getinfo($curl);
        }

        curl_close($curl);

        if ($this->debug) {
            if (!is_resource($out)) {
                throw new UnexpectedValueException('Not a resource.');
            }
            fclose($out);
            $curlDebug = ob_get_clean();

            $this->log[] = ['curl info' => $curlInfo];
            $this->log[] = ['curl verbose' => $curlDebug];
            $this->log[] = ['curl response' => $resp];
        }

        if (!is_string($resp)) {
            throw new UnexpectedValueException('Response is not valid.');
        }

        [$header, $body] = explode("\r\n\r\n", $resp, 2);
        $body = trim($body);
        if ($body === '') {
            throw new UnexpectedValueException(sprintf('Empty body. header: %s', $header));
        }

        return $body;
    }
    // @phpcs:enable

    /**
     * @phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
     * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
     * @phpcs:disable SlevomatCodingStandard.Files.FunctionLength.FunctionLength
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $data
     * @todo solve phpcs issues
     */
    private function hydrateResponse(array $data): MagNewsResponse
    {
        foreach (self::RESPONSE_FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                /**
                 * Sometimes magnews returns a response in a different format.
                 * Eg. {"error":"Internal Server Error"}
                 */
                if (array_key_exists('error', $data)) {
                    throw new UnexpectedValueException(sprintf('MagNews error: "%s".', $data['error']));
                }

                throw new OutOfBoundsException(sprintf('Response is missing required field "%s".', $field));
            }
        }

        if (!is_bool($data['ok'])) {
            throw new UnexpectedValueException('Value is not of the expected type.');
        }

        foreach (['pk', 'action'] as $field) {
            if (!is_string($data[$field])) {
                throw new UnexpectedValueException('Value is not of the expected type.');
            }
        }

        if (!is_int($data['idcontact'])) {
            throw new UnexpectedValueException('Value is not of the expected type.');
        }

        return new MagNewsResponse(
            $data['ok'],
            $data['pk'],
            $data['idcontact'],
            $data['action'],
            is_array($data['errors'])
                ? $data['errors']
                : null,
            is_array($data['sendemail'])
                ? $data['sendemail']
                : null,
            is_array($data['enterworkflow'])
                ? $data['enterworkflow']
                : null,
        );
    }
    // @phpcs:enable
}
