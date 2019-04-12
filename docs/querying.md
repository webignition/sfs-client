# Querying

`Client::query()` takes a [`Request`](https://github.com/webignition/sfs-client/blob/master/src/Request.php)
as its only argument and returns a [`ResultSetInterface`](https://github.com/webignition/sfs-result-interfaces) 
instance.
 
Use [`RequestFactory::create()`](https://github.com/webignition/sfs-client/blob/master/src/RequestFactory.php)
to create a request.
 
Convenience methods exist for easily querying against single values:

- `Client::queryEmail()` 
- `Client::queryEmailHash()`
- `Client::queryIp()`
- `Client::queryUsername()`

Query convenience methods return a [`ResultInterface`](https://github.com/webignition/sfs-result-interfaces)
instance, or `null` if the HTTP request failed.

The examples below cover this more clearly.

### Querying By Example

```php
use webignition\SfsClient\Client;
use webignition\SfsClient\Request;
use webignition\SfsClient\RequestFactory;
use webignition\SfsResultInterfaces\ResultInterface;

$client = new Cient();

// Query against a single email address
$result = $client->queryEmail('user@example.com');

// $result will be NULL if the HTTP request to query api.stopforumspam.com failed for any reason

if ($result instanceof ResultInterface) {
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

foreach ($resultSet as $result) {
    // ...
}

// Query against email hashes
$result = $client->queryEmailHash('e959a91017a2718f759d6375ee52ddc9');

$resultSet = $client->query(RequestFactory::create([
    RequestFactory::KEY_EMAIL_HASHES => [
        'e959a91017a2718f759d6375ee52ddc9',
        // ...
    ],
]));

// Query against IP addresses
$result = $client->queryIp('127.0.0.1');

$resultSet = $client->query(RequestFactory::create([
    RequestFactory::KEY_IPS => [
        '127.0.0.1',
        // ...
    ],
]));

// Query against usernames
$result = $client->queryUsername('user1');

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
