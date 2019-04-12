# SFS Client

PHP HTTP client for querying [api.stopforumspam.com][sfs-usage].

## Installation

`composer require webignition/sfs-client`

## api.stopformumspam.com overview

[api.stopforumspam.com][sfs-usage] can be queried by email address, email hash, ip address
or username. Optional flags can be provided to influence what types of results are returned.

Read the [api.stopforumspam.com usage guide][sfs-usage] first if unfamiliar.

## Usage

- [create client](/docs/creating-a-client.md)
- optionally [create a request](/docs/creating-a-request.md) if not using a single-value convenience method
- [query](/docs/querying.md) for results

## Quick Usage Example
```php
use webignition\SfsClient\Client;
use webignition\SfsResultInterfaces\ResultInterface;

$client = new Cient();

// Query against a single email address
$result = $client->queryEmail('user@example.com');

// $result will be NULL if the HTTP request to query api.stopforumspam.com failed for any reason

if ($result instanceof ResultInterface) {
    $result->getType();                 // 'email', 'emailHash', 'ip' or 'username'
    $result->getFrequency();            // int
    $result->getAppears();              // bool
    $result->getValue();                // the email address, email hash, IP address or username
    $result->getLastSeen()              // \DateTime()|null
    $result->getConfidence()            // float|null
    $result->getDelegatedCountryCode(); // string|null
    $result->getCountryCode();          // string|null
    $result->getAsn();                  // int|null
    $result->isBlacklisted();           // bool
    $result->isTorExitNode();           // bool|null
}
```

Read more about [creating requests](/docs/creating-a-request.md) and [querying](/docs/querying.md).

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

