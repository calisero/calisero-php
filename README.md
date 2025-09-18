# Calisero PHP SMS API Library

[![Latest Version on Packagist](https://img.shields.io/packagist/v/calisero/calisero-php.svg?style=flat-square)](https://packagist.org/packages/calisero/calisero-php)
[![tests](https://github.com/calisero/calisero-php/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/calisero/calisero-php/actions/workflows/ci.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat-square)](https://phpstan.org)
[![License](https://img.shields.io/packagist/l/calisero/calisero-php.svg?style=flat-square)](https://packagist.org/packages/calisero/calisero-php)
[![Tests](https://img.shields.io/github/actions/workflow/status/calisero/calisero-php/ci.yml?branch=main&label=tests&style=flat-square)](https://github.com/calisero/calisero-php/actions/workflows/ci.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/calisero/calisero-php.svg?style=flat-square)](https://packagist.org/packages/calisero/calisero-php)

**Official PHP library for the [Calisero](https://calisero.ro) transactional SMS API.**

Send SMS messages, manage opt-outs for GDPR compliance, and monitor your account—all with a simple, type-safe PHP library that just works.

## Features

- 🚀 **Simple & Intuitive**: Easy-to-use API with clean factory methods
- 🔒 **Type Safe**: Full PHP type declarations and PHPStan level 9 compliance  
- 🌐 **HTTP Client Included**: Uses reliable Guzzle HTTP client out of the box
- 🔄 **Idempotency**: Built-in idempotency key generation for safe retries
- 📱 **Complete API Coverage**: All Calisero SMS API endpoints supported
- 🛡️ **Error Handling**: Comprehensive exception hierarchy for different error types
- 📖 **Rich Examples**: 14+ working examples covering every use case
- ⚡ **Zero Configuration**: Works immediately after installation
- 🏗️ **Production Ready**: Used in production by businesses worldwide

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

## Getting Your API Key

To use this library, you'll need an API key from your Calisero account:

1. **Log in to your Calisero account** at [https://calisero.ro](https://calisero.ro)
2. **Navigate to the dashboard** and go to the **"API Keys"** section
3. **Click "Add Key"** to create a new API key
4. **Configure your key:**
   - Give it a descriptive name (e.g., "Production App", "Development")
   - Set IP filters if needed for additional security
   - Choose appropriate permissions
5. **Copy your API key** and store it securely

### API Key Management

From the API Keys section in your dashboard, you can:
- ✅ **Add new keys** for different applications or environments
- ✏️ **Edit existing keys** to update names or permissions
- 🗑️ **Delete keys** that are no longer needed
- 🛡️ **Add IP filters** to restrict key usage to specific IP addresses
- 📊 **Monitor key usage** and activity logs

> **Security Note**: Never commit API keys to version control. Use environment variables or secure configuration files.

## Quick Start

### Basic Usage

```php
use Calisero\Sms\Sms;

// Create a client with your API key
$client = Sms::client('your-api-key-here');

// Send a simple SMS
$request = new \Calisero\Sms\Dto\CreateMessageRequest(
    recipient: '+40742***350',
    sender: 'CALISEOR',
    body: 'Hello from Calisero!'
);

$response = $client->messages()->create($request);
```

### Environment Configuration

For production applications, store your API key securely:

```bash
# .env file
CALISERO_API_KEY=your-api-key-here
```

```php
// In your application
$client = Sms::client($_ENV['CALISERO_API_KEY']);
```

## 🎯 Common Use Cases

### OTP/2FA Authentication
```php
$request = new CreateMessageRequest(
    recipient: $userPhoneNumber,
    body: "Your verification code is: AC3-4F6. Valid for 5 minutes.",
    visibleBody: "Your verification code is: ******. Valid for 5 minutes.",
    validity: 1, // 1 hour validity
    sender: 'YourApp'
);
$response = $client->messages()->create($request);
```

### Order Notifications
```php
$request = new CreateMessageRequest(
    recipient: $customerPhone,
    body: "Order #{$orderNumber} confirmed! Estimated delivery: {$deliveryDate}",
    callbackUrl: "https://yourstore.com/webhooks/sms/dlr",
    sender: 'YourStore'
);
```

### Marketing Campaigns with Opt-out Compliance
```php
// Check if user is opted out first
try {
    $client->optOuts()->get($phoneNumber);
    echo "User is opted out, skipping message";
} catch (NotFoundException $e) {
    // User is not opted out, safe to send
    $request = new CreateMessageRequest(
        recipient: $phoneNumber,
        body: "Special offer! Get 20% off with code SAVE20. Reply STOP to opt out.",
        sender: 'YourBrand'
    );
    $client->messages()->create($request);
}
```

### Advanced Message Creation

```php
use Calisero\Sms\Dto\CreateMessageRequest;

$request = new CreateMessageRequest(
    recipient: '+40742***350',
    body: 'Your verification code is: 123456',
    visibleBody: 'Your verification code is: ******',       // For logs/display
    validity: 24,                                           // 24 hours validity
    scheduleAt: '2024-12-25 10:00:00',                      // Schedule for later
    callbackUrl: 'https://yoursite.com/webhook',            // Delivery reports
    sender: 'Calisero'                                      // Custom sender
);

$response = $client->messages()->create($request);
```

## Authentication

### Using API Key (Bearer Token)

```php
use Calisero\Sms\Sms;

$client = Sms::client('your-api-key-here');
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

## 📚 Examples

This library includes comprehensive examples for all operations. Check the [`examples/`](examples/) directory for detailed usage patterns:

### 📱 Message Examples
- **[`examples/messages/send_simple_sms.php`](examples/messages/send_simple_sms.php)** - Send basic SMS messages
- **[`examples/messages/send_advanced_sms.php`](examples/messages/send_advanced_sms.php)** - Advanced SMS with scheduling, callbacks, custom sender
- **[`examples/messages/send_bulk_sms.php`](examples/messages/send_bulk_sms.php)** - Bulk SMS with rate limiting and error handling
- **[`examples/messages/get_sms.php`](examples/messages/get_sms.php)** - Retrieve message details and status
- **[`examples/messages/list_sms.php`](examples/messages/list_sms.php)** - List messages with pagination
- **[`examples/messages/delete_sms.php`](examples/messages/delete_sms.php)** - Cancel scheduled messages

### 🚫 OptOut Examples (GDPR Compliance)
- **[`examples/optouts/create_optout.php`](examples/optouts/create_optout.php)** - Add phone numbers to opt-out list
- **[`examples/optouts/get_optout.php`](examples/optouts/get_optout.php)** - Check opt-out status
- **[`examples/optouts/list_optouts.php`](examples/optouts/list_optouts.php)** - List all opt-outs with pagination
- **[`examples/optouts/update_optout.php`](examples/optouts/update_optout.php)** - Update opt-out reasons
- **[`examples/optouts/delete_optout.php`](examples/optouts/delete_optout.php)** - Remove opt-out (re-enable SMS)

### 👤 Account Examples
- **[`examples/account/get_account.php`](examples/account/get_account.php)** - Get account information and details
- **[`examples/account/check_balance.php`](examples/account/check_balance.php)** - Check balance with analysis and recommendations

### 🛡️ Error Handling
- **[`examples/error_handling_complete.php`](examples/error_handling_complete.php)** - Comprehensive error handling for all exception types

### 🚀 Running Examples

1. Clone this repository and install dependencies:
   ```bash
   git clone https://github.com/calisero/calisero-php.git
   cd calisero-php
   composer install
   ```

2. Set your API key in any example file:
   ```php
   $bearerToken = 'your-api-key-here'; // Replace with your actual API key
   ```

3. Run an example:
   ```bash
   php examples/messages/send_simple_sms.php
   ```

> **Note**: Examples use masked phone numbers (`+40742***350`) for security. Replace with actual phone numbers when testing.

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
    ]
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
