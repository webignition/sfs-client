# Creating a Request

## Use the RequestFactory

You could create a `Request` by hand but that can get tedious for larger payloads.

Use [`RequestFactory::create()`](https://github.com/webignition/sfs-client/blob/master/src/RequestFactory.php):

```php
use webignition\SfsClient\RequestFactory;

// To query against one or more email addresses
$request = RequestFactory::create([
    RequestFactory::KEY_EMAILS => [
        'user1@example.com',
        // ...
    ],
]);

// To query against one or more email hashes
$request = RequestFactory::create([
    RequestFactory::KEY_EMAIL_HASHES => [
        'e959a91017a2718f759d6375ee52ddc9',
        // ...
    ],
]);

// To query against one or more IP addresses
$request = RequestFactory::create([
    RequestFactory::KEY_IPS => [
        '127.0.0.1',
        // ...
    ],
]);

// To query against one or more usernames
$request = RequestFactory::create([
    RequestFactory::KEY_USERNAMES => [
        'user1',
        // ...
    ],
]);

```