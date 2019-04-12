# SFS Client

PHP HTTP client for querying [api.stopforumspam.com][sfs-usage].

## Installation

`composer require webignition/sfs-client`

## api.stopformumspam.com overview

[api.stopforumspam.com][sfs-usage] can be queried by email address, email hash, ip address
or username. Optional flags can be provided to influence what types of results are returned.

Read the [api.stopforumspam.com usage guide][sfs-usage] first if unfamiliar.

## Usage

### Creating a Default Client

```php
use webignition\SfsClient\Client;

$client = new Client();
```

### Creating a Custom Client

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

### Querying

`Client::query()` takes a `webignition\SfsClient\Request` as its only argument and returns a
[`ResultSetInterface`](https://github.com/webignition/sfs-result-models) instance.

The examples below cover this more clearly.

### Querying By Example

```php
use webignition\SfsClient\Client;
use webignition\SfsClient\Request;
use webignition\SfsClient\RequestFactory;

$client = new Cient();

// Query against a single email address
$resultSet = $client->queryEmail('user@example.com');

foreach ($resultSet as $result) {
    $result->getType();                 // 'email', 'emailHash', 'ip' or 'username'
    $result->getFrequency();            // int
    $result->getAppears();              // bool
    $result->getValue();                // value queried against (the email address, emailHash, IP address or username
    $result->getLastSeen()              // \DateTime()|null
    $result->getConfidence()            // float|null
    $result->getDelegatedCountryCode(); // string|null
    $result->getCountryCode();          // string|null
    $result->getAsn();                  // int|null
    $result->isBlacklisted();           // bool
    $result->isTorExitNode();           // bool|null
}

// Query against multiple email addresses
$resultSet = $client->query(RequestFactory::create([
    RequestFactory::KEY_EMAILS => [
        'user1@example.com',
        'user1@example.com', // duplicate are ok, they will be ignored
        'user2@example.com',
        'USER2@example.com', // values are case-insensitve, this duplicate will be ignored
    ],
]));

// Query against email hashes
$resultSet = $client->queryEmailHash('e959a91017a2718f759d6375ee52ddc9');

$resultSet = $client->query(RequestFactory::create([
    RequestFactory::KEY_EMAIL_HASHES => [
        'e959a91017a2718f759d6375ee52ddc9',
        // ...
    ],
]));

// Query against IP addresses
$resultSet = $client->queryIp('127.0.0.1');

$resultSet = $client->query(RequestFactory::create([
    RequestFactory::KEY_IPS => [
        '127.0.0.1',
        // ...
    ],
]));

// Query against usernames
$resultSet = $client->queryUsername('user1');

$resultSet = $client->query(RequestFactory::create([
    RequestFactory::KEY_USERNAMES => [
        'user1',
        // ...
    ],
]));

// Add options
$resultSet = $client->query(RequestFactory::create([
    RequestFactory::KEY_IPS => [
        '127.0.0.1',
        // ...
    ],
    RequestFactory::KEY_OPTIONS => [
        Request::OPTION_NO_BAD_IP => true, // any value that evaluate to true is fine
    ],    
]));

// Query against multiple values
$resultSet = $client->query(RequestFactory::create([
    RequestFactory::KEY_EMAILS => [
        'user1@example.com',
        'user2@example.com',
    ],
    RequestFactory::KEY_EMAIL_HASHES => [
        'e959a91017a2718f759d6375ee52ddc9',
    ],    
    RequestFactory::KEY_IPS => [
        '127.0.0.1',
    ],   
    RequestFactory::KEY_USERNAMES => [
        'user1',
    ],     
]));

```

## Understanding and Analysing Results

You probably want to know if a given email address/IP address/username can be trusted 
to perform an operation within your application.

See [webignition/sfs-result-analyser](https://github.com/webignition/sfs-result-analyser)
for help with that.

## See Also
Use [webignition/sfs-querier](https://github.com/webignition/sfs-querier) for a package that
contains [webignition/sfs-result-analyser](https://github.com/webignition/sfs-result-analyser),
[webignition/sfs-client](https://github.com/webignition/sfs-client) and provides detailed
usage instructions.

[sfs-usage]: https://www.stopforumspam.com/usage

