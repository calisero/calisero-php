# Calisero PHP SMS API Library

[![Latest Version on Packagist](https://img.shields.io/packagist/v/calisero/calisero-php.svg?style=flat-square)](https://packagist.org/packages/calisero/calisero-php)
[![tests](https://github.com/calisero/calisero-php/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/calisero/calisero-php/actions/workflows/ci.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat-square)](https://phpstan.org)
[![License](https://img.shields.io/packagist/l/calisero/calisero-php.svg?style=flat-square)](https://packagist.org/packages/calisero/calisero-php)
[![Tests](https://img.shields.io/github/actions/workflow/status/calisero/calisero-php/ci.yml?branch=main&label=tests&style=flat-square)](https://github.com/calisero/calisero-php/actions/workflows/ci.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/calisero/calisero-php.svg?style=flat-square)](https://packagist.org/packages/calisero/calisero-php)

Official PHP library for the [Calisero](https://calisero.ro) transactional SMS API.

## Features

- 🚀 **Simple & Intuitive**: Easy-to-use API with clean factory methods
- 🔒 **Type Safe**: Full PHP type declarations and PHPStan level 9 compliance
- 🌐 **HTTP Client Included**: Uses reliable Guzzle HTTP client out of the box
- 🔄 **Idempotency**: Built-in idempotency key generation for safe retries
- 📱 **Complete API Coverage**: All Calisero SMS API endpoints supported
- 🛡️ **Error Handling**: Comprehensive exception hierarchy for different error types
- 📖 **Well Documented**: Extensive documentation and examples
- ⚡ **Zero Configuration**: Works immediately after installation

## Requirements

- PHP 7.4 or higher
- `ext-json` extension
- `ext-curl` extension (for HTTP requests)

## Installation

Install the library via Composer:

```bash
composer require calisero/calisero-php
```

The library uses Guzzle HTTP client and includes all necessary dependencies automatically.

## Quick Start

### Basic Usage

```php
use Calisero\Sms\Sms;

// Create a client
$client = Sms::client('your-api-token');

// Send a simple SMS
$request = new \Calisero\Sms\Dto\CreateMessageRequest(
    to: '+40742***350',
    from: 'Calisero',
    body: 'Hello from Calisero!'
);

$response = $client->messages()->create($request);
```

### Advanced Message Creation

```php
use Calisero\Sms\Dto\CreateMessageRequest;

$request = new CreateMessageRequest(
    recipient: '+40742***350',
    body: 'Your verification code is: 123456',
    visibleBody: 'Your verification code is: ******', // For logs/display
    validity: 24,                                     // 24 hours validity
    scheduleAt: '2024-12-25 10:00:00',               // Schedule for later
    callbackUrl: 'https://yoursite.com/webhook',     // Delivery reports
    sender: 'Calisero'                                // Custom sender
);

$response = $client->messages()->create($request);
```

## Authentication

### Using Bearer Token

```php
use Calisero\Sms\Sms;

$client = Sms::client('your-bearer-token');
```

### Custom Authentication Provider

```php
use Calisero\Sms\Contracts\AuthProviderInterface;
use Calisero\Sms\SmsClient;

class CustomAuthProvider implements AuthProviderInterface
{
    public function getToken(): string
    {
        // Your custom token logic here
        return $this->fetchTokenFromSomewhere();
    }
}

// Note: This requires using SmsClient directly
$authProvider = new CustomAuthProvider();
$client = new SmsClient(
    $httpClient, 
    $requestFactory, 
    $streamFactory, 
    $authProvider
);
```

## API Reference

### Messages

#### Send a Message

```php
use Calisero\Sms\Dto\CreateMessageRequest;

$request = new CreateMessageRequest(
    recipient: '+40742***350',
    body: 'Hello World!'
);

$response = $client->messages()->create($request);
$message = $response->getData();

echo $message->getId();        // Message UUID
echo $message->getStatus();    // Message status
echo $message->getParts();     // Number of SMS parts
```

#### Get Message Details

```php
$response = $client->messages()->get('message-uuid-here');
$message = $response->getData();

echo $message->getRecipient();
echo $message->getBody();
echo $message->getStatus();
echo $message->getSentAt();
echo $message->getDeliveredAt();
```

#### List Messages

```php
// Get first page
$response = $client->messages()->list();

foreach ($response->getData() as $message) {
    echo $message->getId() . ': ' . $message->getBody() . PHP_EOL;
}

// Pagination
echo 'Current page: ' . $response->getMeta()->getCurrentPage();
echo 'Total per page: ' . $response->getMeta()->getPerPage();

// Get next page if available
if ($response->getLinks()->getNext()) {
    $nextPage = $client->messages()->list(2);
}
```

#### Delete a Message

```php
try {
    $client->messages()->delete('message-uuid-here');
    echo "Message deleted successfully";
} catch (\Calisero\Sms\Exceptions\ForbiddenException $e) {
    echo "Cannot delete: message already sent";
}
```

### Opt-outs

#### Create an Opt-out

```php
use Calisero\Sms\Dto\CreateOptOutRequest;

$request = new CreateOptOutRequest(
    phone: '+40742***350',
    reason: 'User requested opt-out via website'
);

$response = $client->optOuts()->create($request);
```

#### List Opt-outs

```php
$response = $client->optOuts()->list();

foreach ($response->getData() as $optOut) {
    echo $optOut->getPhone() . ': ' . $optOut->getReason() . PHP_EOL;
}
```

#### Update an Opt-out

```php
use Calisero\Sms\Dto\UpdateOptOutRequest;

$request = new UpdateOptOutRequest(
    phone: '+40742***350',
    reason: 'Updated reason'
);

$response = $client->optOuts()->update('optout-uuid-here', $request);
```

#### Delete an Opt-out

```php
$client->optOuts()->delete('optout-uuid-here');
```

### Accounts

#### Get Account Information

```php
$response = $client->accounts()->get('account-uuid-here');
$account = $response->getData();

echo 'Account: ' . $account->getName();
echo 'Credit: ' . $account->getCredit();
echo 'Status: ' . $account->getStatus();
echo 'Sandbox: ' . ($account->isSandbox() ? 'Yes' : 'No');
```

## Error Handling

The library provides specific exception types for different error scenarios:

```php
use Calisero\Sms\Exceptions\{
    ApiException,
    UnauthorizedException,
    ForbiddenException,
    NotFoundException,
    ValidationException,
    RateLimitedException,
    ServerException,
    TransportException
};

try {
    $response = $client->messages()->create($request);
} catch (UnauthorizedException $e) {
    // Handle authentication errors (401)
    echo "Invalid or expired token";
} catch (ValidationException $e) {
    // Handle validation errors (422)
    echo "Validation error: " . $e->getMessage();
    foreach ($e->getValidationErrors() as $field => $errors) {
        echo "$field: " . implode(', ', $errors);
    }
} catch (RateLimitedException $e) {
    // Handle rate limiting (429)
    echo "Rate limited. Retry after: " . $e->getRetryAfter() . " seconds";
} catch (ServerException $e) {
    // Handle server errors (5xx)
    echo "Server error: " . $e->getMessage();
} catch (TransportException $e) {
    // Handle network/transport errors
    echo "Network error: " . $e->getMessage();
} catch (ApiException $e) {
    // Handle any other API errors
    echo "API error: " . $e->getMessage();
    echo "Status code: " . $e->getStatusCode();
    echo "Request ID: " . $e->getRequestId();
}
```

## Advanced Configuration

### Custom HTTP Client

```php
use Calisero\Sms\Sms;
use Calisero\Sms\Contracts\HttpClientInterface;
use GuzzleHttp\Client as GuzzleClient;

// Create a custom HTTP client wrapper
$httpClient = new class(new GuzzleClient(['timeout' => 60])) implements HttpClientInterface {
    private GuzzleClient $client;
    
    public function __construct(GuzzleClient $client) {
        $this->client = $client;
    }
    
    public function sendRequest($request) {
        return $this->client->sendRequest($request);
    }
};

$client = Sms::clientWith(
    bearerToken: 'your-token',
    httpClient: $httpClient
);
```

### Custom Options

```php
// Create client with custom Guzzle options
$client = Sms::client(
    bearerToken: 'your-token', 
    baseUri: null,
    options: [
        'timeout' => 60,
        'connect_timeout' => 10,
        'proxy' => 'http://proxy.example.com:8080'
    ]
);
```

### Custom Base URI

```php
// For sandbox testing
$client = Sms::client(
    bearerToken: 'your-sandbox-token',
    baseUri: 'https://sandbox.calisero.ro/api/v1'
);

// For custom API endpoints
$client = Sms::client(
    bearerToken: 'your-token',
    baseUri: 'https://api.custom-endpoint.com/v1'
);
```

### Custom Idempotency Key Provider

```php
use Calisero\Sms\Contracts\IdempotencyKeyProviderInterface;
use Calisero\Sms\SmsClient;

class CustomIdempotencyProvider implements IdempotencyKeyProviderInterface
{
    public function generate(): string
    {
        return 'custom-' . uniqid() . '-' . time();
    }
}

// Note: This requires using SmsClient directly instead of Sms::client()
$client = new SmsClient(
    $httpClient,
    $requestFactory,
    $streamFactory,
    $authProvider,
    'https://rest.calisero.ro/api/v1',
    new CustomIdempotencyProvider()
);
```

## Testing

The library includes comprehensive test coverage:

```bash
# Run tests
composer test

# Run tests with coverage
composer test -- --coverage-html coverage

# Run static analysis
composer stan

# Run code style checks
composer lint

# Run all quality assurance checks
composer qa
```

### Testing Your Implementation

```php
// In your tests, you can mock the HTTP client
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class YourSmsTest extends TestCase
{
    public function testSendMessage()
    {
        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        
        $mockResponse->method('getStatusCode')->willReturn(201);
        $mockResponse->method('getBody')->willReturn(json_encode([
            'data' => [
                'id' => 'test-uuid',
                'recipient' => '+40742***350',
                'body' => 'Test message',
                'status' => 'scheduled',
                // ... other fields
            ]
        ]));
        
        $mockClient->method('sendRequest')->willReturn($mockResponse);
        
        // Create client with mocked dependencies
        $client = Sms::clientWith('test-token', $mockClient);
        
        // Test your implementation
        $request = new CreateMessageRequest('+40742***350', 'Test message');
        $response = $client->messages()->create($request);
        
        $this->assertEquals('test-uuid', $response->getData()->getId());
    }
}
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## Security

If you discover any security-related issues, please email support@calisero.ro instead of using the issue tracker.

## License

This library is open-sourced software licensed under the [MIT license](LICENSE.md).

## Support

- 📧 Email: support@calisero.ro
- 📖 Documentation: [https://docs.calisero.ro](https://docs.calisero.ro)
- 🐛 Issues: [GitHub Issues](https://github.com/calisero/calisero-php/issues)

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.
