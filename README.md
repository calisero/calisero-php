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
- ✂️ **URL Shortening**: Shorten the links in a message body and track their clicks
- 📊 **Limits & Tracing**: Daily sending limit, request rate limit and the trace ID of every message, verification and error
- 📬 **Delivery Webhooks**: Typed, validated parsing of delivery status callbacks
- 🛡️ **Error Handling**: Comprehensive exception hierarchy for different error types
- 📖 **Rich Examples**: 14+ working examples covering every use case
- ⚡ **Zero Configuration**: Works immediately after installation
- 🏗️ **Production Ready**: Used in production by businesses worldwide
- 📦 **Minimal Dependencies**: Only requires PHP and basic extensions
- 🎯 **Laravel Integration**: Official Laravel wrapper `calisero/laravel-sms` available
- 🐘 **Wide PHP Support**: Runs on PHP 7.4 through PHP 8.5

## Requirements

- PHP 7.4 or higher — tested on **7.4, 8.0, 8.1, 8.2, 8.3, 8.4 and 8.5**
- `ext-json` extension
- `ext-curl` extension

The library carries no runtime dependencies beyond those extensions, so it drops
into any supported PHP version without a resolver conflict.

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
    body: 'Your code is 123456. Manage alerts: https://yoursite.com/alerts',
    visibleBody: 'Your code is ******. Manage alerts: https://yoursite.com/alerts', // For logs/display
    validity: 24,                                           // 24 hours validity
    scheduleAt: '2024-12-25 10:00:00',                      // Schedule for later
    callbackUrl: 'https://yoursite.com/webhook',            // Delivery reports
    sender: 'Calisero',                                     // Custom sender
    shortenUrls: true                                       // Shorten the links of the body
);

$response = $client->messages()->create($request);
echo "Advanced message created with ID: " . $response->getData()->getId() . "\n";
```

> **PHP 7.4**: named arguments need PHP 8.0. On PHP 7.4 pass the arguments in the
> constructor's order: `recipient`, `body`, `visibleBody`, `validity`, `scheduleAt`,
> `callbackUrl`, `sender`, `shortenUrls` (use `null` for the ones you skip).

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
```

`SmsClient::create()` takes the API key itself. To authenticate through a provider
like this one, build the HTTP client and the services yourself, as shown in
[Building the Client Yourself](#building-the-client-yourself).

## 📚 Examples

This library includes comprehensive examples for all operations. Check the [`examples/`](examples/) directory for detailed usage patterns:

### 📱 Message Examples
- **[`examples/messages/send_simple_sms.php`](examples/messages/send_simple_sms.php)** - Send basic SMS messages
- **[`examples/messages/send_advanced_sms.php`](examples/messages/send_advanced_sms.php)** - Advanced SMS with scheduling, callbacks, custom sender and URL shortening
- **[`examples/messages/send_bulk_sms.php`](examples/messages/send_bulk_sms.php)** - Bulk SMS with rate limiting, error handling and the daily sending limit
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
- **[`examples/account/check_balance.php`](examples/account/check_balance.php)** - Check balance and daily sending limit with analysis and recommendations

### 📬 Webhook Examples
- **[`examples/webhooks/delivery_webhook.php`](examples/webhooks/delivery_webhook.php)** - Receive delivery status callbacks

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

// What the answer's headers report
$meta = $response->getResponseMeta();
echo $meta->getTraceId();         // The request's trace ID
echo $meta->getDailyRemaining();  // Messages left today (null: no daily limit)
```

#### Shorten URLs

Set `shortenUrls` to `true` and Calisero replaces every `http://` and `https://`
link of the body with a short one before sending, so the SMS gets shorter (and may
take fewer parts). Each shortened link comes back with its click statistics:

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateMessageRequest;

$client = SmsClient::create('your-api-key-here');

$request = new CreateMessageRequest(
    recipient: '+40742***350',
    body: 'Your order has shipped! Track it: https://yourstore.com/orders/12345/tracking',
    shortenUrls: true
);

$message = $client->messages()->create($request)->getData();

foreach ($message->getShortenedUrls() as $link) {
    echo $link->getOriginalLink();   // https://yourstore.com/orders/12345/tracking
    echo $link->getShortenedLink();  // https://calisero.ro/s/ghJKPV
}

// Later: how many times the link was opened
$message = $client->messages()->get($message->getId())->getData();

foreach ($message->getShortenedUrls() as $link) {
    echo $link->getClickCount();     // Number of clicks
    echo $link->getLastClick();      // Last click timestamp, or null
}
```

Links are not shortened unless you ask: leave `shortenUrls` out (or pass `false`)
and the body is sent as written.

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

### Verifications (OTP)

#### Create a Verification

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateVerificationRequest;

$client = SmsClient::create('your-api-key-here');

$request = new CreateVerificationRequest(
    '+40742***350', // phone
    'Calisero'      // optional brand used for default template
);

$response = $client->verifications()->create($request);
$verification = $response->getData();

echo $verification->getId();       // Verification UUID
echo $verification->getPhone();    // Phone number
echo $verification->getStatus();   // 'unverified' or 'verified'

// The code's SMS counts towards the account's daily sending limit
echo $response->getResponseMeta()->getDailyRemaining(); // Messages left today (null: no daily limit)
```

#### Get Verification Details

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;

$client = SmsClient::create('your-api-key-here');

$response = $client->verifications()->get('verification-uuid-here');
$verification = $response->getData();

echo $verification->getStatus();      // Current status
echo $verification->getVerifiedAt();  // Timestamp or null
echo $verification->getExpiresAt();   // Expiry timestamp
```

#### List Verifications

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;

$client = SmsClient::create('your-api-key-here');

// Page 1, optionally filter by status: 'verified' or 'unverified'
$response = $client->verifications()->list(1, null);

foreach ($response->getData() as $v) {
    echo $v->getId() . ' | ' . $v->getPhone() . ' | ' . $v->getStatus() . PHP_EOL;
}

// Pagination metadata
echo 'Current page: ' . $response->getMeta()->getCurrentPage();
if ($response->getLinks()->getNext()) {
    // Fetch next page example
    $next = $client->verifications()->list(2);
}
```

#### Validate a Verification Code (OTP)

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\VerificationCheckRequest;

$client = SmsClient::create('your-api-key-here');

$request = new VerificationCheckRequest(
    '+40742***350', // phone
    'ABC123'        // 6-character code
);

$response = $client->verifications()->validate($request);
$verification = $response->getData();

echo $verification->getStatus();     // 'verified' (idempotent if already verified)
echo $verification->getVerifiedAt(); // When it was verified
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

// Daily sending limit (null when no daily limit applies)
echo 'Daily limit: ' . ($account->getDailyLimit() ?? 'none') . "\n";
echo 'Left today: ' . ($account->getDailyRemaining() ?? 'unlimited') . "\n";
echo 'Sent today: ' . $account->getSentToday() . "\n";
```

### Delivery Status Webhooks

When a message has a `callbackUrl`, Calisero posts a JSON payload to it each time
the message's status changes (`sent`, `delivered` or `undelivered`).
`DeliveryWebhookMessage` reads and validates it:

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\Dto\DeliveryWebhookMessage;

try {
    $webhook = DeliveryWebhookMessage::fromJson((string) file_get_contents('php://input'));
} catch (InvalidArgumentException $e) {
    http_response_code(400); // Malformed payload
    exit;
}

echo $webhook->getMessageId();        // The message's UUID
echo $webhook->getStatus();           // 'sent', 'delivered' or 'undelivered'
echo $webhook->getDeliveredAt();      // Set for 'delivered'
echo $webhook->getPrice();            // Price charged for the message's parts
echo $webhook->getRemainingBalance(); // Account balance after billing
echo $webhook->getDailyRemaining();   // Messages left today when it was sent (null: no daily limit)

// Answer with any 2xx within 2 seconds
http_response_code(200);
echo '{"received":true}';
```

Only a failed connection or an answer slower than 2 seconds is retried (at most 5
attempts in total); a non-2xx answer is not. Keep the endpoint fast: record the
status or queue a job, then answer. In a framework, pass the decoded request body
to `DeliveryWebhookMessage::fromArray()` instead.

## Daily Sending Limit

Every account has its own daily sending limit: how many messages it can send in a
day. A new account starts with a default limit and keeps it until Calisero raises
it on request.

- Every real message counts once, whatever its number of parts: the messages of
  `messages()->create()` and the OTP codes of `verifications()->create()` alike.
  A scheduled message counts on the day it is created.
- Test messages (a sandbox account or a sandbox API key) never count and are never refused.
- The day ends at midnight, Romania time (Europe/Bucharest), whatever time zone you send from.

You can see the limit and what is left of it in three places:

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\SmsClient;
use Calisero\Sms\Dto\CreateMessageRequest;

$client = SmsClient::create('your-api-key-here');

// 1. On the account
$account = $client->accounts()->get('account-uuid-here')->getData();
echo $account->getDailyLimit();      // e.g. 1000, or null when no daily limit applies
echo $account->getDailyRemaining();  // e.g. 873
echo $account->getSentToday();       // e.g. 127

// 2. After every message or verification created (X-Daily-Limit / X-Daily-Remaining)
$response = $client->messages()->create(new CreateMessageRequest(
    recipient: '+40742***350',
    body: 'Hello!'
));
echo $response->getResponseMeta()->getDailyLimit();      // e.g. 1000
echo $response->getResponseMeta()->getDailyRemaining();  // e.g. 872, after this message

// 3. In every delivery status callback: DeliveryWebhookMessage::getDailyRemaining()
```

Once the limit is reached, new messages and verifications are refused with a
`DailyLimitExceededException` until midnight, Romania time. Nothing is sent and
nothing is billed:

```php
use Calisero\Sms\Exceptions\DailyLimitExceededException;

try {
    $client->messages()->create($request);
} catch (DailyLimitExceededException $e) {
    echo $e->getMessage();         // What happened and when the limit resets
    echo $e->getDailyLimit();      // e.g. 1000
    echo $e->getDailyRemaining();  // 0
    echo $e->getResetsAt();        // e.g. 2026-10-01T00:00:00+03:00
    echo $e->getRetryAfter();      // Seconds until the reset
}
```

To raise the limit, contact Calisero with your account and the daily volume you expect.

## Rate Limits & Trace ID

### Request Rate Limit

Separately from the daily sending limit, each API user may make **240 requests a
minute**, on every endpoint. Above that the API answers `429` and the library throws
a `RateLimitedException`:

```php
use Calisero\Sms\Exceptions\DailyLimitExceededException;
use Calisero\Sms\Exceptions\RateLimitedException;

try {
    $client->messages()->create($request);
} catch (DailyLimitExceededException $e) {
    // The daily sending limit: wait until $e->getResetsAt()
} catch (RateLimitedException $e) {
    // The request rate limit: wait a few seconds and retry
    sleep($e->getRetryAfter() ?? 1);
    echo $e->getRateLimitLimit();      // 240
    echo $e->getRateLimitRemaining();  // 0
    echo $e->getRateLimitReset();      // Unix timestamp when requests are accepted again
}
```

`DailyLimitExceededException` extends `RateLimitedException`, so catch it first.
After a successful create, `getResponseMeta()->getRateLimitRemaining()` tells how many
requests are left in the current minute.

### Trace ID

Every API answer carries the request's trace ID. Log it: quoting it to support
lets Calisero find the request at once, and the dashboard shows everything that
happened to it under **Developers → Debug**.

```php
use Calisero\Sms\Exceptions\ApiException;

// On success
echo $client->messages()->create($request)->getResponseMeta()->getTraceId();

// On error
try {
    $client->messages()->create($request);
} catch (ApiException $e) {
    error_log('Calisero request ' . $e->getTraceId() . ' failed: ' . $e->getMessage());
}
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
    DailyLimitExceededException,
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
} catch (DailyLimitExceededException $e) {
    // Handle the daily sending limit (429): nothing was sent, wait for the reset
    echo "Daily limit reached. Sending resumes at: " . $e->getResetsAt() . "\n";
} catch (RateLimitedException $e) {
    // Handle the request rate limit (429)
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
    echo "Trace ID: " . $e->getTraceId() . "\n";
}
```

Every `ApiException` carries the API's own error message (`getMessage()`), the HTTP
status (`getStatusCode()`), the request's trace ID (`getTraceId()`; `getRequestId()`
returns the same value) and the decoded error body (`getErrorDetails()`).

| Exception | When |
|---|---|
| `UnauthorizedException` | `401`: invalid or missing API key |
| `ForbiddenException` | `403`: not allowed, e.g. deleting a message already sent |
| `NotFoundException` | `404`: the resource does not exist |
| `ValidationException` | `422`: invalid request data; field errors in `getValidationErrors()` |
| `DailyLimitExceededException` | `429`: the account reached its daily sending limit |
| `RateLimitedException` | `429`: more than 240 requests a minute |
| `ServerException` | `500`, `502`, `503`, `504` |
| `ApiException` | Any other API error; base class of the above |
| `TransportException` | The request got no answer (network error, timeout) |

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

`SmsClient::create()` sends every request to `https://rest.calisero.ro/api/v1` with
a 30-second timeout (10 seconds to connect), authenticates with the API key you
pass, and adds a random UUID `Idempotency-Key` header to each message and
verification it creates. Every request names the library, PHP and the platform in
its `User-Agent` header, e.g. `Calisero-SMS-PHP/2.3.1 (PHP 8.5.3; linux x86_64)`.

### Building the Client Yourself

`SmsClient::create()` is the only way to get an `SmsClient`, and its setup is
fixed. For anything else (your own authentication or idempotency key provider,
other timeouts, or the raw response to each request), build the HTTP client and
the services yourself: every service takes the `HttpClient` in its constructor.

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\Auth\BearerTokenAuthProvider;
use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Http\BaseHttpClient;
use Calisero\Sms\Http\Factory\HttpFactory;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\IdempotencyKey\UuidIdempotencyKeyProvider;
use Calisero\Sms\Services\AccountService;
use Calisero\Sms\Services\MessageService;
use Calisero\Sms\Services\OptOutService;
use Calisero\Sms\Services\VerificationService;

$httpClient = new HttpClient(
    new BaseHttpClient(60, 5),                          // Timeout and connect timeout, in seconds
    new HttpFactory(),
    new BearerTokenAuthProvider('your-api-key-here'),   // Or your own AuthProviderInterface
    'https://rest.calisero.ro/api/v1',
    new UuidIdempotencyKeyProvider()                    // Or your own IdempotencyKeyProviderInterface
);

// The same services SmsClient returns from messages(), verifications(), optOuts() and accounts()
$messages = new MessageService($httpClient);
$verifications = new VerificationService($httpClient);
$optOuts = new OptOutService($httpClient);
$accounts = new AccountService($httpClient);

$messages->create(new CreateMessageRequest(
    recipient: '+40742***350',
    body: 'Hello!'
));

$account = $accounts->get('account-uuid-here')->getData();

// The raw response to the last request, headers included: here, the trace ID of the GET
echo $httpClient->getLastResponse()?->getHeaderLine('X-Trace-Id');
```

### Custom Idempotency Key Provider

The library sends an `Idempotency-Key` header with each message and verification it
creates, a random UUID by default. To generate the key yourself, implement
`IdempotencyKeyProviderInterface` and pass your provider as the last argument of
`HttpClient`:

```php
<?php

require_once 'vendor/autoload.php';

use Calisero\Sms\Auth\BearerTokenAuthProvider;
use Calisero\Sms\Contracts\IdempotencyKeyProviderInterface;
use Calisero\Sms\Http\BaseHttpClient;
use Calisero\Sms\Http\Factory\HttpFactory;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\Services\MessageService;

class CustomIdempotencyProvider implements IdempotencyKeyProviderInterface
{
    public function generate(): string
    {
        return 'custom-' . uniqid() . '-' . time();
    }
}

$httpClient = new HttpClient(
    new BaseHttpClient(30, 10),
    new HttpFactory(),
    new BearerTokenAuthProvider('your-api-key-here'),
    'https://rest.calisero.ro/api/v1',
    new CustomIdempotencyProvider()
);

$messages = new MessageService($httpClient);
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

`SmsClient::create()` builds its own HTTP client, so let the code you want to test
receive the service it uses, such as a `MessageService`: pass it
`$client->messages()` in production. In tests, build the service on a mocked
`HttpClient` that returns the API's JSON body, decoded:

```php
<?php

use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\Services\MessageService;
use PHPUnit\Framework\TestCase;

class YourSmsTest extends TestCase
{
    public function testSendMessage(): void
    {
        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->once())
            ->method('post')
            ->with('/messages', ['recipient' => '+40742***350', 'body' => 'Hello!'], true)
            ->willReturn([
                'data' => [
                    'id' => '9e2574e8-3615-4090-9b5a-0fc812079da8',
                    'recipient' => '+40742***350',
                    'body' => 'Hello!',
                    'parts' => 1,
                    'created_at' => '2025-02-06T10:18:43.000000Z',
                    'status' => 'scheduled',
                ],
            ]);

        $messages = new MessageService($httpClient);
        $message = $messages->create(new CreateMessageRequest('+40742***350', 'Hello!'))->getData();

        $this->assertSame('scheduled', $message->getStatus());
    }
}
```

The library's own service tests, in [`tests/Unit/Services`](tests/Unit/Services),
work the same way; [TESTING.md](TESTING.md) describes the whole suite.

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
