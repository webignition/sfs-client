<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\SfsClient\Tests;

use PHPUnit\Framework\TestCase;
use webignition\SfsClient\Request;

class RequestTest extends TestCase
{
    /**
     * @var Request
     */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new Request();
    }

    public function testAddEmailsNoData()
    {
        $this->request->addEmails([]);
        $this->request->addEmails([]);

        $this->assertEquals([], $this->request->getPayload());
    }

    /**
     * @dataProvider addDataProvider
     */
    public function testAddEmails(array $values, array $additionalValues, array $expectedFieldPayload)
    {
        $this->request->addEmails($values);
        $this->request->addEmails($additionalValues);

        $this->assertEquals($expectedFieldPayload, $this->request->getPayload()['email']);
    }

    /**
     * @dataProvider addDataProvider
     */
    public function testAddEmailHashes(array $values, array $additionalValues, array $expectedFieldPayload)
    {
        $this->request->addEmailHashes($values);
        $this->request->addEmailHashes($additionalValues);

        $this->assertEquals($expectedFieldPayload, $this->request->getPayload()['emailhash']);
    }

    /**
     * @dataProvider addDataProvider
     */
    public function testAddIps(array $values, array $additionalValues, array $expectedFieldPayload)
    {
        $this->request->addIps($values);
        $this->request->addIps($additionalValues);

        $this->assertEquals($expectedFieldPayload, $this->request->getPayload()['ip']);
    }

    /**
     * @dataProvider addDataProvider
     */
    public function testAddUsernames(array $values, array $additionalValues, array $expectedFieldPayload)
    {
        $this->request->addUsernames($values);
        $this->request->addUsernames($additionalValues);

        $this->assertEquals($expectedFieldPayload, $this->request->getPayload()['username']);
    }

    public function addDataProvider(): array
    {
        return [
            'no values, no additional values' => [
                'values' => [],
                'additionalValues' => [
                    'user1',
                ],
                'expectedFieldPayload' => [
                    'user1',
                ],
            ],
            'values, no additional values' => [
                'values' => [
                    'user1',
                ],
                'additionalValues' => [],
                'expectedFieldPayload' => [
                    'user1',
                ],
            ],
            'values and additional values' => [
                'values' => [
                    'user1',
                ],
                'additionalValues' => [
                    'user2',
                ],
                'expectedFieldPayload' => [
                    'user1',
                    'user2',
                ],
            ],
            'remove duplicates' => [
                'values' => [
                    'user1',
                ],
                'additionalValues' => [
                    'user1',
                    'user2',
                ],
                'expectedFieldPayload' => [
                    'user1',
                    'user2',
                ],
            ],
            'is case-insensitive' => [
                'values' => [
                    'user1',
                ],
                'additionalValues' => [
                    'USER2',
                ],
                'expectedFieldPayload' => [
                    'user1',
                    'user2',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getPayloadDataProvider
     */
    public function testGetPayload(
        array $emails,
        array $emailHashes,
        array $ips,
        array $usernames,
        array $expectedPayload
    ) {
        $this->request->addEmails($emails);
        $this->request->addEmailHashes($emailHashes);
        $this->request->addIps($ips);
        $this->request->addUsernames($usernames);

        $this->assertEquals($expectedPayload, $this->request->getPayload());
    }

    public function getPayloadDataProvider(): array
    {
        return [
            'no data' => [
                'emails' => [],
                'emailHashes' => [],
                'ips' => [],
                'usernames' => [],
                'expectedPayload' => [],
            ],
            'emails only' => [
                'emails' => [
                    'user1@example.com',
                    'user2@example.com',
                ],
                'emailHashes' => [],
                'ips' => [],
                'usernames' => [],
                'expectedPayload' => [
                    'email' => [
                        'user1@example.com',
                        'user2@example.com',
                    ],
                ],
            ],
            'email hashes only' => [
                'emails' => [],
                'emailHashes' => [
                    'ff76391f3e272f1e2a3e91d60d9c5d8f',
                    'b43b23acbc1b28c9462a65df42dbfd50',
                ],
                'ips' => [],
                'usernames' => [],
                'expectedPayload' => [
                    'emailhash' => [
                        'ff76391f3e272f1e2a3e91d60d9c5d8f',
                        'b43b23acbc1b28c9462a65df42dbfd50',
                    ],
                ],
            ],
            'ips only' => [
                'emails' => [],
                'emailHashes' => [],
                'ips' => [
                    '127.0.0.1',
                    '255.255.255.255',
                ],
                'usernames' => [],
                'expectedPayload' => [
                    'ip' => [
                        '127.0.0.1',
                        '255.255.255.255',
                    ],
                ],
            ],
            'usernames only' => [
                'emails' => [],
                'emailHashes' => [],
                'ips' => [],
                'usernames' => [
                    'user1',
                    'user2',
                ],
                'expectedPayload' => [
                    'username' => [
                        'user1',
                        'user2',
                    ],
                ],
            ],
            'all types' => [
                'emails' => [
                    'user1@example.com',
                    'user2@example.com',
                ],
                'emailHashes' => [
                    'ff76391f3e272f1e2a3e91d60d9c5d8f',
                    'b43b23acbc1b28c9462a65df42dbfd50',
                ],
                'ips' => [
                    '127.0.0.1',
                    '255.255.255.255',
                ],
                'usernames' => [
                    'user1',
                    'user2',
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
                ],
            ],
        ];
    }
}
