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
- 🌐 **Self-Contained**: Built-in HTTP client using cURL, no external dependencies
- 🔄 **Idempotency**: Built-in idempotency key generation for safe retries
- 📱 **Complete API Coverage**: All Calisero SMS API endpoints supported
- 🛡️ **Error Handling**: Comprehensive exception hierarchy for different error types
- 📖 **Rich Examples**: 14+ working examples covering every use case
- ⚡ **Zero Configuration**: Works immediately after installation
- 🏗️ **Production Ready**: Used in production by businesses worldwide
- � **Minimal Dependencies**: Only requires PHP and basic extensions
- 🎯 **Laravel Integration**: Official Laravel wrapper `calisero/laravel-sms` available

## Requirements

- PHP 7.4 or higher
- `ext-json` extension
- `ext-curl` extension

## Installation

Install the library via Composer:

```bash
composer require calisero/calisero-php
```

That's it! The library includes its own HTTP client implementation and requires no additional dependencies.

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

### Basic SMS Sending

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateMessageRequest;

// Create the SMS client
$client = SmsClient::create('your-api-key-here');

// Send a simple SMS
$request = new CreateMessageRequest(
    recipient: '+40742***350',
    sender: 'CALISERO',
    body: 'Hello from Calisero!'
);

$response = $client->messages()->create($request);
$message = $response->getData();

echo "Message sent! ID: " . $message->getId() . "\n";
echo "Status: " . $message->getStatus() . "\n";
```

### Environment Configuration

For production applications, store your API key securely:

```bash
# .env file
CALISERO_API_KEY=your-api-key-here
```

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;

// In your application
$client = SmsClient::create($_ENV['CALISERO_API_KEY']);
```

## 🎯 Common Use Cases

### OTP/2FA Authentication
```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateMessageRequest;

$client = SmsClient::create('your-api-key-here');

$request = new CreateMessageRequest(
    recipient: $userPhoneNumber,
    body: "Your verification code is: AC3-4F6. Valid for 5 minutes.",
    visibleBody: "Your verification code is: ******. Valid for 5 minutes.",
    validity: 1, // 1 hour validity
    sender: 'YourApp'
);

$response = $client->messages()->create($request);
echo "OTP sent! Message ID: " . $response->getData()->getId() . "\n";
```

### Order Notifications
```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateMessageRequest;

$client = SmsClient::create('your-api-key-here');

$request = new CreateMessageRequest(
    recipient: $customerPhone,
    body: "Order #{$orderNumber} confirmed! Estimated delivery: {$deliveryDate}",
    callbackUrl: "https://yourstore.com/webhooks/sms/dlr",
    sender: 'YourStore'
);

$response = $client->messages()->create($request);
echo "Order notification sent!\n";
```

### Marketing Campaigns with Opt-out Compliance
```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Exceptions\NotFoundException;

$client = SmsClient::create('your-api-key-here');

// Check if user is opted out first
try {
    $client->optOuts()->get($phoneNumber);
    echo "User is opted out, skipping message\n";
} catch (NotFoundException $e) {
    // User is not opted out, safe to send
    $request = new CreateMessageRequest(
        recipient: $phoneNumber,
        body: "Special offer! Get 20% off with code SAVE20. Reply STOP to opt out.",
        sender: 'YourBrand'
    );
    
    $response = $client->messages()->create($request);
    echo "Marketing message sent!\n";
}
```

### Advanced Message Creation

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateMessageRequest;

$client = SmsClient::create('your-api-key-here');

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
echo "Advanced message created with ID: " . $response->getData()->getId() . "\n";
```

## Authentication

### Using API Key (Bearer Token)

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;

$client = SmsClient::create('your-api-key-here');
```

### Custom Authentication Provider

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\Contracts\AuthProviderInterface;

class CustomAuthProvider implements AuthProviderInterface
{
    public function getToken(): string
    {
        // Your custom token logic here
        return $this->fetchTokenFromSomewhere();
    }
}

// Note: Custom auth providers require direct SmsClient usage
// Contact support@calisero.ro for advanced authentication examples
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

### ✅ Verification Examples (OTP)
- **[`examples/verifications/create_verification.php`](examples/verifications/create_verification.php)** - Start a verification (send OTP)
- **[`examples/verifications/get_verification.php`](examples/verifications/get_verification.php)** - Retrieve verification details
- **[`examples/verifications/list_verifications.php`](examples/verifications/list_verifications.php)** - List verifications with pagination and status filter
- **[`examples/verifications/validate_verification.php`](examples/verifications/validate_verification.php)** - Validate an OTP code for a phone number

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
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateMessageRequest;

$client = SmsClient::create('your-api-key-here');

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
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;

$client = SmsClient::create('your-api-key-here');

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
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;

$client = SmsClient::create('your-api-key-here');

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
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Exceptions\ForbiddenException;

$client = SmsClient::create('your-api-key-here');

try {
    $client->messages()->delete('message-uuid-here');
    echo "Message deleted successfully";
} catch (ForbiddenException $e) {
    echo "Cannot delete: message already sent";
}
```

### Opt-outs

#### Create an Opt-out

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateOptOutRequest;

$client = SmsClient::create('your-api-key-here');

$request = new CreateOptOutRequest(
    phone: '+40742***350',
    reason: 'User requested opt-out via website'
);

$response = $client->optOuts()->create($request);
echo "Opt-out created with ID: " . $response->getData()->getId() . "\n";
```

#### List Opt-outs

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;

$client = SmsClient::create('your-api-key-here');

$response = $client->optOuts()->list();

foreach ($response->getData() as $optOut) {
    echo $optOut->getPhone() . ': ' . $optOut->getReason() . PHP_EOL;
}
```

#### Update an Opt-out

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\UpdateOptOutRequest;

$client = SmsClient::create('your-api-key-here');

$request = new UpdateOptOutRequest(
    phone: '+40742***350',
    reason: 'Updated reason'
);

$response = $client->optOuts()->update('optout-uuid-here', $request);
echo "Opt-out updated successfully\n";
```

#### Delete an Opt-out

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;

$client = SmsClient::create('your-api-key-here');

$client->optOuts()->delete('optout-uuid-here');
echo "Opt-out deleted successfully\n";
```

### Accounts

#### Get Account Information

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;

$client = SmsClient::create('your-api-key-here');

$response = $client->accounts()->get('account-uuid-here');
$account = $response->getData();

echo 'Account: ' . $account->getName() . "\n";
echo 'Credit: ' . $account->getCredit() . "\n";
echo 'Status: ' . $account->getStatus() . "\n";
echo 'Sandbox: ' . ($account->isSandbox() ? 'Yes' : 'No') . "\n";
```

## Error Handling

The library provides specific exception types for different error scenarios:

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateMessageRequest;
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

$client = SmsClient::create('your-api-key-here');

$request = new CreateMessageRequest(
    recipient: '+40742***350',
    body: 'Test message'
);

try {
    $response = $client->messages()->create($request);
    echo "Message sent successfully!\n";
} catch (UnauthorizedException $e) {
    // Handle authentication errors (401)
    echo "Invalid or expired token\n";
} catch (ValidationException $e) {
    // Handle validation errors (422)
    echo "Validation error: " . $e->getMessage() . "\n";
    foreach ($e->getValidationErrors() as $field => $errors) {
        echo "$field: " . implode(', ', $errors) . "\n";
    }
} catch (RateLimitedException $e) {
    // Handle rate limiting (429)
    echo "Rate limited. Retry after: " . $e->getRetryAfter() . " seconds\n";
} catch (ServerException $e) {
    // Handle server errors (5xx)
    echo "Server error: " . $e->getMessage() . "\n";
} catch (TransportException $e) {
    // Handle network/transport errors
    echo "Network error: " . $e->getMessage() . "\n";
} catch (ApiException $e) {
    // Handle any other API errors
    echo "API error: " . $e->getMessage() . "\n";
    echo "Status code: " . $e->getStatusCode() . "\n";
    echo "Request ID: " . $e->getRequestId() . "\n";
}
```

## Advanced Configuration

### Simple Client Creation

The library uses a simplified approach with built-in HTTP client:

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;

// Simple client creation - all you need
$client = SmsClient::create('your-api-key-here');
```

The library uses an optimized cURL-based HTTP client internally, providing excellent performance without external dependencies.

### Custom Idempotency Key Provider

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\Contracts\IdempotencyKeyProviderInterface;
use Calisero\Sms\SmsClient;

class CustomIdempotencyProvider implements IdempotencyKeyProviderInterface
{
    public function generate(): string
    {
        return 'custom-' . uniqid() . '-' . time();
    }
}

// Note: This requires using SmsClient directly instead of SmsClient::create()
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
<?php

// In your tests, you can mock the HTTP client
use PHPUnit\Framework\TestCase;
use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Contracts\HttpClientInterface;

class YourSmsTest extends TestCase
{
    public function testSendMessage()
    {
        $mockClient = $this->createMock(HttpClientInterface::class);
        
        // Set up your mock expectations for testing
        // Use the library's built-in interfaces for clean testing
        
        $client = SmsClient::create('test-token');
        // ... your test implementation
    }
}
                // ... other fields
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

- **Documentation**: [Official API Docs](https://docs.calisero.ro/)  
- **GitHub Issues**: [Report Issues](https://github.com/calisero/calisero-php/issues)  
- **Email Support**: support@calisero.ro

- 📧 Email: support@calisero.ro
- 📖 Documentation: [https://docs.calisero.ro](https://docs.calisero.ro)
- 🐛 Issues: [GitHub Issues](https://github.com/calisero/calisero-php/issues)

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.
