<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\SfsClient\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use webignition\SfsClient\HttpRequestFactory;
use webignition\SfsClient\Request;
use webignition\SfsClient\RequestFactory;

class HttpRequestFactoryTest extends TestCase
{
    /**
     * @var HttpRequestFactory
     */
    private $httpRequestFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpRequestFactory = new HttpRequestFactory();
    }

    public function testCreateHttpRequestEmptyRequestPayload()
    {
        $this->assertNull($this->httpRequestFactory->create(new Request()));
    }

    public function testCreateHttpRequestCustomApiBaseUrl()
    {
        $customApiBaseUrl = 'https://api.example.com/api';

        $request = new Request();
        $request->addIps([
            '127.0.0.1',
        ]);

        $httpRequestFactory = new HttpRequestFactory($customApiBaseUrl);
        $httpRequest = $httpRequestFactory->create($request);

        if ($httpRequest instanceof RequestInterface) {
            $this->assertInstanceOf(RequestInterface::class, $httpRequest);
            $this->assertEquals($customApiBaseUrl, $httpRequest->getUri());
        }
    }

    /**
     * @dataProvider createHttpRequestSuccessDataProvider
     */
    public function testCreateHttpRequestSuccess(Request $request, array $expectedPostData)
    {
        $httpRequest = $this->httpRequestFactory->create($request);

        if ($httpRequest instanceof RequestInterface) {
            $this->assertInstanceOf(RequestInterface::class, $httpRequest);
            $this->assertEquals(HttpRequestFactory::API_BASE_URL, $httpRequest->getUri());
            $this->assertEquals('POST', $httpRequest->getMethod());
            $this->assertEquals('application/x-www-form-urlencoded', $httpRequest->getHeaderLine('content-type'));

            $postData = [];
            parse_str(rawurldecode($httpRequest->getBody()->getContents()), $postData);

            $this->assertEquals($expectedPostData, $postData);
        }
    }

    public function createHttpRequestSuccessDataProvider(): array
    {
        return [
            'simple payload' => [
                'request' => RequestFactory::create([
                    RequestFactory::KEY_EMAILS => [
                        'user1@example.com',
                    ],
                ]),
                'expectedPostData' => [
                    'email' => [
                        'user1@example.com',
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
                        HttpRequestFactory::FORMAT_XML_CDATA,
                        HttpRequestFactory::FORMAT_XML_DOM,
                        HttpRequestFactory::FORMAT_PHP_SERIAL,
                        HttpRequestFactory::FORMAT_JSON_P,
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
}
