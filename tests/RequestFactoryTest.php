<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\SfsClient\Tests;

use PHPUnit\Framework\TestCase;
use webignition\SfsClient\Request;
use webignition\SfsClient\RequestFactory;

class RequestFactoryTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(array $data, array $expectedPayload)
    {
        $request = RequestFactory::create($data);

        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals($expectedPayload, $request->getPayload());
    }

    public function createDataProvider(): array
    {
        return [
            'no data' => [
                'data' => [],
                'expectedPayload' => [],
            ],
            'emails only' => [
                'data' => [
                    RequestFactory::KEY_EMAILS => [
                        'user1@example.com',
                        'user2@example.com',
                    ],
                ],
                'expectedPayload' => [
                    'email' => [
                        'user1@example.com',
                        'user2@example.com',
                    ],
                ],
            ],
            'email hashes only' => [
                'data' => [
                    RequestFactory::KEY_EMAIL_HASHES => [
                        'ff76391f3e272f1e2a3e91d60d9c5d8f',
                        'b43b23acbc1b28c9462a65df42dbfd50',
                    ],
                ],
                'expectedPayload' => [
                    'emailhash' => [
                        'ff76391f3e272f1e2a3e91d60d9c5d8f',
                        'b43b23acbc1b28c9462a65df42dbfd50',
                    ],
                ],
            ],
            'ips only' => [
                'data' => [
                    RequestFactory::KEY_IPS => [
                        '127.0.0.1',
                        '255.255.255.255',
                    ],
                ],
                'expectedPayload' => [
                    'ip' => [
                        '127.0.0.1',
                        '255.255.255.255',
                    ],
                ],
            ],
            'usernames only' => [
                'data' => [
                    RequestFactory::KEY_USERNAMES => [
                        'user1',
                        'user2',
                    ],
                ],
                'expectedPayload' => [
                    'username' => [
                        'user1',
                        'user2',
                    ],
                ],
            ],
            'options only' => [
                'data' => [
                    RequestFactory::KEY_OPTIONS => [
                        Request::OPTION_NO_BAD_EMAIL,
                        Request::OPTION_NO_BAD_USERNAME,
                        Request::OPTION_NO_BAD_IP,
                        Request::OPTION_NO_BAD_ALL,
                        Request::OPTION_NO_TOR_EXIT,
                        Request::OPTION_BAD_TOR_EXIT,
                    ],
                ],
                'expectedPayload' => [
                    Request::OPTION_NO_BAD_EMAIL => true,
                    Request::OPTION_NO_BAD_USERNAME => true,
                    Request::OPTION_NO_BAD_IP => true,
                    Request::OPTION_NO_BAD_ALL => true,
                    Request::OPTION_NO_TOR_EXIT => true,
                    Request::OPTION_BAD_TOR_EXIT => true,
                ],
            ],
            'mixed' => [
                'data' => [
                    RequestFactory::KEY_EMAILS => [
                        'user1@example.com',
                        'user2@example.com',
                    ],
                    RequestFactory::KEY_EMAIL_HASHES => [
                        'ff76391f3e272f1e2a3e91d60d9c5d8f',
                        'b43b23acbc1b28c9462a65df42dbfd50',
                    ],
                    RequestFactory::KEY_IPS => [
                        '127.0.0.1',
                        '255.255.255.255',
                    ],
                    RequestFactory::KEY_USERNAMES => [
                        'user1',
                        'user2',
                    ],
                    RequestFactory::KEY_OPTIONS => [
                        Request::OPTION_NO_BAD_EMAIL,
                        Request::OPTION_NO_BAD_USERNAME,
                        Request::OPTION_NO_BAD_IP,
                        Request::OPTION_NO_BAD_ALL,
                        Request::OPTION_NO_TOR_EXIT,
                        Request::OPTION_BAD_TOR_EXIT,
                    ],
                ],
                'expectedPayload' => [
                    'email' => [
                        'user1@example.com',
                        'user2@example.com',
                    ],
                    'emailhash' => [
                        'ff76391f3e272f1e2a3e91d60d9c5d8f',
                        'b43b23acbc1b28c9462a65df42dbfd50',
                    ],
                    'ip' => [
                        '127.0.0.1',
                        '255.255.255.255',
                    ],
                    'username' => [
                        'user1',
                        'user2',
                    ],
                    Request::OPTION_NO_BAD_EMAIL => true,
                    Request::OPTION_NO_BAD_USERNAME => true,
                    Request::OPTION_NO_BAD_IP => true,
                    Request::OPTION_NO_BAD_ALL => true,
                    Request::OPTION_NO_TOR_EXIT => true,
                    Request::OPTION_BAD_TOR_EXIT => true,
                ],
            ],
        ];
    }
}
