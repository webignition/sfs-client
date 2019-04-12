<?php

namespace webignition\SfsClient;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface as HttpRequest;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use webignition\SfsResultFactory\ResultSetFactory;
use webignition\SfsResultInterfaces\ResultInterface;
use webignition\SfsResultInterfaces\ResultSetInterface;
use webignition\SfsResultModels\ResultSet;

class Client
{
    const FORMAT_XML_CDATA = 'xmlcdata';
    const FORMAT_XML_DOM = 'xmldom';
    const FORMAT_PHP_SERIAL = 'serial';
    const FORMAT_JSON_P = 'jsonp';
    const FORMAT_JSON = 'json';
    const API_BASE_URL = 'https://api.stopforumspam.com/api';

    private $disallowedFormats = [
        self::FORMAT_XML_CDATA,
        self::FORMAT_XML_DOM,
        self::FORMAT_PHP_SERIAL,
        self::FORMAT_JSON_P,
    ];

    private $apiBaseUrl;

    private $httpClient;
    private $resultSetFactory;

    public function __construct(
        string $apiBaseUrl = self::API_BASE_URL,
        ?HttpClient $httpClient = null,
        ?ResultSetFactory $resultSetFactory = null
    ) {
        $this->apiBaseUrl = $apiBaseUrl;
        $this->httpClient = $httpClient ?? new HttpClient();
        $this->resultSetFactory = $resultSetFactory ?? new ResultSetFactory();
    }

    public function query(Request $request): ResultSetInterface
    {
        $requestPayload = $this->normaliseRequestPayload($request);
        if (empty($requestPayload)) {
            return new ResultSet();
        }

        $requestPayload[self::FORMAT_JSON] = true;

        $httpRequest = new \GuzzleHttp\Psr7\Request(
            'POST',
            $this->apiBaseUrl,
            ['content-type' => 'application/x-www-form-urlencoded'],
            http_build_query($requestPayload, '', '&')
        );

        $httpResponse = $this->getHttpResponse($httpRequest);
        if (empty($httpResponse)) {
            return new ResultSet();
        }

        if (!$this->isApplicationJsonResponse($httpResponse)) {
            return new ResultSet();
        }

        $responseData = json_decode($httpResponse->getBody()->getContents(), true);
        if (!is_array($responseData)) {
            return new ResultSet();
        }

        return $this->resultSetFactory->create($responseData);
    }

    public function queryEmail(string $email, array $options = []): ?ResultInterface
    {
        return $this->querySingleValue($email, RequestFactory::KEY_EMAILS, $options);
    }

    public function queryEmailHash(string $emailHash, array $options = []): ?ResultInterface
    {
        return $this->querySingleValue($emailHash, RequestFactory::KEY_EMAIL_HASHES, $options);
    }

    public function queryIp(string $ip, array $options = []): ?ResultInterface
    {
        return $this->querySingleValue($ip, RequestFactory::KEY_IPS, $options);
    }

    public function queryUsername(string $username, array $options = []): ?ResultInterface
    {
        return $this->querySingleValue($username, RequestFactory::KEY_USERNAMES, $options);
    }

    private function querySingleValue(string $value, string $type, array $options = []): ?ResultInterface
    {
        $resultSet = $this->query(RequestFactory::create([
            $type => [
                $value,
            ],
            RequestFactory::KEY_OPTIONS => $options,
        ]));

        return count($resultSet) > 0
            ? $resultSet->current()
            : null;
    }

    private function getHttpResponse(HttpRequest $request): ?HttpResponse
    {
        $httpResponse = null;

        try {
            $httpResponse = $this->httpClient->send($request);
        } catch (GuzzleException $e) {
        }

        return $httpResponse;
    }

    private function isApplicationJsonResponse(HttpResponse $response): bool
    {
        $contentType = 'application/json';
        $contentTypeHeaderLine = $response->getHeaderLine('content-type');

        return substr($contentTypeHeaderLine, 0, strlen($contentType)) === $contentType;
    }

    private function normaliseRequestPayload(Request $request): array
    {
        $payload = $request->getPayload();
        foreach ($this->disallowedFormats as $disallowedFormat) {
            if (array_key_exists($disallowedFormat, $payload)) {
                unset($payload[$disallowedFormat]);
            }
        }

        return $payload;
    }
}
