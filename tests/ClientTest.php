<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\SfsClient\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as HttpResponse;
use PHPUnit\Framework\TestCase;
use webignition\SfsClient\Client;
use webignition\SfsClient\HttpRequestFactory;
use webignition\SfsClient\Request;
use webignition\SfsClient\RequestFactory;
use webignition\SfsResultFactory\ResultSetFactory;
use webignition\SfsResultInterfaces\ResultSetInterface;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var MockHandler
     */
    private $httpMockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpMockHandler = new MockHandler();

        $httpClient = new HttpClient([
            'handler' => HandlerStack::create($this->httpMockHandler),
        ]);
        $httpRequestFactory = new HttpRequestFactory();
        $resultSetFactory = new ResultSetFactory();
        $this->client = new Client($httpClient, $httpRequestFactory, $resultSetFactory);
    }

    public function testCreate()
    {
        $client = Client::create();

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testQueryEmptyPayload()
    {
        $resultSet = $this->client->query(new Request());

        $this->assertInstanceOf(ResultSetInterface::class, $resultSet);
        $this->assertEmpty($resultSet);
    }

    /**
     * @dataProvider queryInvalidResponseDataProvider
     */
    public function testQueryInvalidResponse(HttpResponse $httpFixture)
    {
        $this->httpMockHandler->append($httpFixture);

        $resultSet = $this->client->query(RequestFactory::create([
            RequestFactory::KEY_IPS => [
                '127.0.0.1',
            ],
        ]));

        $this->assertInstanceOf(ResultSetInterface::class, $resultSet);
        $this->assertEmpty($resultSet);
    }

    public function queryInvalidResponseDataProvider(): array
    {
        return [
            'HTTP 404' => [
                'httpFixture' => new HttpResponse(404),
            ],
            'Invalid response content type' => [
                'httpFixture' => new HttpResponse(200, ['content-type' => 'text/plain']),
            ],
            'Invalid response body' => [
                'httpFixture' => new HttpResponse(
                    200,
                    ['content-type' => 'application/json'],
                    (string) json_encode(false)
                ),
            ],
        ];
    }

    /**
     * @dataProvider querySuccessDataProvider
     */
    public function testQuerySuccess(Request $request, HttpResponse $httpFixture, ResultSetInterface $expectedResultSet)
    {
        $this->httpMockHandler->append($httpFixture);

        $resultSet = $this->client->query($request);

        $this->assertInstanceOf(ResultSetInterface::class, $resultSet);
        $this->assertEquals($expectedResultSet, $resultSet);
    }

    public function querySuccessDataProvider(): array
    {
        $resultSetFactory = new ResultSetFactory();

        return [
            'single ip request, no appearances' => [
                'request' => RequestFactory::create([
                    RequestFactory::KEY_IPS => [
                        '127.0.0.1',
                    ],
                ]),
                'httpFixture' => $this->createJsonResponse([
                    'success' => 1,
                    'ip' => [
                        [
                            'value' => '127.0.0.1',
                            'frequency' => 0,
                            'appears' => 0,
                            'asn' => null,
                        ],
                    ],
                ]),
                'expectedResultSet' => $resultSetFactory->create([
                    'success' => 1,
                    'ip' => [
                        [
                            'value' => '127.0.0.1',
                            'frequency' => 0,
                            'appears' => 0,
                            'asn' => null,
                        ],
                    ],
                ]),
            ],
        ];
    }

    private function createJsonResponse(array $data): HttpResponse
    {
        return new HttpResponse(
            200,
            [
                'content-type' => 'application/json',
            ],
            (string) json_encode($data)
        );
    }
}
