<?php

namespace webignition\SfsClient;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface as HttpRequest;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use webignition\SfsResultFactory\ResultSetFactory;
use webignition\SfsResultInterfaces\ResultSetInterface;
use webignition\SfsResultModels\ResultSet;

class Client
{
    private $httpClient;
    private $httpRequestFactory;
    private $resultSetFactory;

    public function __construct(
        HttpClient $httpClient,
        HttpRequestFactory $httpRequestFactory,
        ResultSetFactory $resultSetFactory
    ) {
        $this->httpClient = $httpClient;
        $this->httpRequestFactory = $httpRequestFactory;
        $this->resultSetFactory = $resultSetFactory;
    }

    public static function create(string $apiBaseUrl = HttpRequestFactory::API_BASE_URL): Client
    {
        return new Client(
            new HttpClient(),
            new HttpRequestFactory($apiBaseUrl),
            new ResultSetFactory()
        );
    }

    public function query(Request $request): ResultSetInterface
    {
        $httpRequest = $this->httpRequestFactory->create($request);
        if (empty($httpRequest)) {
            return new ResultSet();
        }

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
}
