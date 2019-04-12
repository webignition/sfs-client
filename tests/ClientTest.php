<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\SfsClient\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as HttpResponse;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use webignition\SfsClient\Client;
use webignition\SfsClient\Request;
use webignition\SfsClient\RequestFactory;
use webignition\SfsResultFactory\ResultFactory;
use webignition\SfsResultFactory\ResultSetFactory;
use webignition\SfsResultInterfaces\ResultInterface;
use webignition\SfsResultInterfaces\ResultSetInterface;
use webignition\SfsResultModels\Result;

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

    /**
     * @var HttpClient
     */
    private $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpMockHandler = new MockHandler();

        $this->httpClient = new HttpClient([
            'handler' => HandlerStack::create($this->httpMockHandler),
        ]);

        $this->client = new Client(
            Client::API_BASE_URL,
            $this->httpClient
        );
    }

    public function testQueryCustomApiUrl()
    {
        $url = 'http://api.example.com/';

        $this->httpMockHandler->append(new Response(404));

        $client = new Client(
            $url,
            $this->httpClient
        );

        $client->query(RequestFactory::create([
            RequestFactory::KEY_IPS => [
                '127.0.0.1',
            ],
        ]));

        $lastRequest = $this->httpMockHandler->getLastRequest();

        $this->assertEquals($url, $lastRequest->getUri());
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
     * @dataProvider queryHttpRequestCreationDataProvider
     */
    public function testQueryHttpRequestCreation(
        Request $request,
        array $expectedPostData
    ) {
        $this->httpMockHandler->append(new HttpResponse(404));
        $this->client->query($request);

        $lastRequest = $this->httpMockHandler->getLastRequest();

        $postData = [];
        parse_str(rawurldecode($lastRequest->getBody()->getContents()), $postData);

        $this->assertEquals($expectedPostData, $postData);
    }

    public function queryHttpRequestCreationDataProvider(): array
    {
        return [
            'single ip request' => [
                'request' => RequestFactory::create([
                    RequestFactory::KEY_IPS => [
                        '127.0.0.1',
                    ],
                ]),
                'expectedPostData' => [
                    'ip' => [
                        '127.0.0.1',
                    ],
                    'json' => '1',
                ],
            ],
            'disallowed request formats are removed' => [
                'request' => RequestFactory::create([
                    RequestFactory::KEY_IPS => [
                        '127.0.0.1',
                    ],
                    RequestFactory::KEY_OPTIONS => [
                        Client::FORMAT_XML_CDATA,
                        Client::FORMAT_XML_DOM,
                        Client::FORMAT_PHP_SERIAL,
                        Client::FORMAT_JSON_P,
                    ],
                ]),
                'expectedPostData' => [
                    'ip' => [
                        '127.0.0.1',
                    ],
                    'json' => '1',
                ],
            ],
        ];
    }

    /**
     * @dataProvider querySuccessDataProvider
     */
    public function testQuerySuccess(
        Request $request,
        HttpResponse $httpFixture,
        ResultSetInterface $expectedResultSet
    ) {
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

    /**
     * @dataProvider queryConvenienceMethodsDataProvider
     */
    public function testQueryConvenienceMethods(
        ResponseInterface $httpFixture,
        callable $executor,
        ?ResultInterface $expectedResult,
        array $expectedPostData
    ) {
        $this->httpMockHandler->append($httpFixture);

        $client = new Client(
            Client::API_BASE_URL,
            $this->httpClient
        );

        $result = $executor($client);
        $this->assertEquals($expectedResult, $result);

        $lastRequest = $this->httpMockHandler->getLastRequest();

        $postData = [];
        parse_str(rawurldecode($lastRequest->getBody()->getContents()), $postData);

        $this->assertSame($expectedPostData, $postData);
    }

    public function queryConvenienceMethodsDataProvider(): array
    {
        $resultFactory = ResultFactory::createFactory();

        return [
            'queryEmail; http request fails' => [
                'httpFixture' => new Response(404),
                'executor' => function (Client $client) {
                    return $client->queryEmail('user@example.com');
                },
                'expectedResult' => null,
                'expectedPostData' => [
                    'email' => [
                        'user@example.com',
                    ],
                    'json' => '1',
                ],
            ],
            'queryEmail; http request succeeds' => [
                'httpFixture' => $this->createJsonResponse([
                    'success' => 1,
                    'email' => [
                        [
                            'value' => 'user@example.com',
                            'frequency' => 0,
                            'appears' => 0,
                        ],
                    ],
                ]),
                'executor' => function (Client $client) {
                    return $client->queryEmail('user@example.com');
                },
                'expectedResult' => $resultFactory->create(
                    [
                        'value' => 'user@example.com',
                        'frequency' => 0,
                        'appears' => 0,
                    ],
                    Result::TYPE_EMAIL
                ),
                'expectedPostData' => [
                    'email' => [
                        'user@example.com',
                    ],
                    'json' => '1',
                ],
            ],
            'queryEmailHash; http request fails' => [
                'httpFixture' => new Response(404),
                'executor' => function (Client $client) {
                    return $client->queryEmailHash('357f447e1fce6d524bb6c59796a418d6');
                },
                'expectedResult' => null,
                'expectedPostData' => [
                    'emailhash' => [
                        '357f447e1fce6d524bb6c59796a418d6',
                    ],
                    'json' => '1',
                ],
            ],
            'queryEmailHash; http request succeeds' => [
                'httpFixture' => $this->createJsonResponse([
                    'success' => 1,
                    'emailHash' => [
                        [
                            'value' => '357f447e1fce6d524bb6c59796a418d6',
                            'frequency' => 0,
                            'appears' => 0,
                        ],
                    ],
                ]),
                'executor' => function (Client $client) {
                    return $client->queryEmailHash('357f447e1fce6d524bb6c59796a418d6');
                },
                'expectedResult' => $resultFactory->create(
                    [
                        'value' => '357f447e1fce6d524bb6c59796a418d6',
                        'frequency' => 0,
                        'appears' => 0,
                    ],
                    Result::TYPE_EMAIL_HASH
                ),
                'expectedPostData' => [
                    'emailhash' => [
                        '357f447e1fce6d524bb6c59796a418d6',
                    ],
                    'json' => '1',
                ],
            ],
            'queryIp; http request fails' => [
                'httpFixture' => new Response(404),
                'executor' => function (Client $client) {
                    return $client->queryIp('127.0.0.1');
                },
                'expectedResult' => null,
                'expectedPostData' => [
                    'ip' => [
                        '127.0.0.1',
                    ],
                    'json' => '1',
                ],
            ],
            'queryIp; http request succeeds' => [
                'httpFixture' => $this->createJsonResponse([
                    'success' => 1,
                    'ip' => [
                        [
                            'value' => '127.0.0.1',
                            'frequency' => 0,
                            'appears' => 0,
                        ],
                    ],
                ]),
                'executor' => function (Client $client) {
                    return $client->queryIp('127.0.0.1');
                },
                'expectedResult' => $resultFactory->create(
                    [
                        'value' => '127.0.0.1',
                        'frequency' => 0,
                        'appears' => 0,
                    ],
                    Result::TYPE_IP
                ),
                'expectedPostData' => [
                    'ip' => [
                        '127.0.0.1',
                    ],
                    'json' => '1',
                ],
            ],
            'queryUsername; http request fails' => [
                'httpFixture' => new Response(404),
                'executor' => function (Client $client) {
                    return $client->queryUsername('user');
                },
                'expectedResult' => null,
                'expectedPostData' => [
                    'username' => [
                        'user',
                    ],
                    'json' => '1',
                ],
            ],
            'queryUsername; http request succeeds' => [
                'httpFixture' => $this->createJsonResponse([
                    'success' => 1,
                    'username' => [
                        [
                            'value' => 'user',
                            'frequency' => 0,
                            'appears' => 0,
                        ],
                    ],
                ]),
                'executor' => function (Client $client) {
                    return $client->queryUsername('user');
                },
                'expectedResult' => $resultFactory->create(
                    [
                        'value' => 'user',
                        'frequency' => 0,
                        'appears' => 0,
                    ],
                    Result::TYPE_USERNAME
                ),
                'expectedPostData' => [
                    'username' => [
                        'user',
                    ],
                    'json' => '1',
                ],
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
