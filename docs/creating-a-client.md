# Creating a Client

## Creating a Default Client

```php
use webignition\SfsClient\Client;

$client = new Client();
```

## Creating a Custom Client

You can provide constructor arguments for:

- api url
- http client

### Using a Custom API URL

The API URL might change (who knows!). Specifying your own helps here.

```php
use webignition\SfsClient\Client;

$client = new Client('https://api.stopforumspam.com/api2');
```

### Using a Custom HTTP Client

If using this package within a large application, you may well have a Guzzle
HTTP client instance to hand that is already configured as needed.

```php
use GuzzleHttp\Client as HttpClient;
use webignition\SfsClient\Client;

$httpClient = new HttpClient();

$client = new Client(Client::API_BASE_URL, $httpClient);
```
