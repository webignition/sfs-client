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

```php
use GuzzleHttp\Client as HttpClient;
use webignition\SfsClient\Client;

// If you want to use a different URL (it may change in the future)
$client = new Client('https://api.stopforumspam.com/api2');

// Providing your own Guzzle HTTP client
$httpClient = new HttpClient();

$client = new Client(
    Client::API_BASE_URL,
    $httpClient()
);
```
