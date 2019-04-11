<?php

namespace webignition\SfsClient;

use GuzzleHttp\Psr7\Request as HttpRequest;
use Psr\Http\Message\RequestInterface;

class HttpRequestFactory
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

    public function __construct(string $apiBaseUrl = self::API_BASE_URL)
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function create(Request $request): ?RequestInterface
    {
        $requestPayloadData = $request->getPayload();
        foreach ($this->disallowedFormats as $disallowedFormat) {
            if (array_key_exists($disallowedFormat, $requestPayloadData)) {
                unset($requestPayloadData[$disallowedFormat]);
            }
        }

        if (empty($requestPayloadData)) {
            return null;
        }

        $requestPayloadData[self::FORMAT_JSON] = true;

        return new HttpRequest(
            'POST',
            $this->apiBaseUrl,
            ['content-type' => 'application/x-www-form-urlencoded'],
            http_build_query($requestPayloadData, '', '&')
        );
    }
}
