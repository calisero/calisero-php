# AGENTS.md: calisero/calisero-php

Guidance for AI coding agents. Calisero is a Romanian transactional SMS API (base URL
`https://rest.calisero.ro/api/v1`, `Authorization: Bearer <API key>`). `calisero/calisero-php` is its official PHP
library (namespace `Calisero\Sms`): PHP 7.4 to 8.5, `ext-curl` and `ext-json`, no other dependency.

In a Laravel application use [`calisero/laravel-sms`](https://github.com/calisero/laravel-sms), in a Symfony
application [`calisero/calisero-symfony`](https://github.com/calisero/calisero-symfony): both are built on this
library and add configuration, a webhook route and events.

- **Using this library**: you are adding Calisero to an application.
- **Working on this library**: you are changing this repository.

Every name below exists in `src/`; `README.md` documents each call in full.

## Using this library

### Install and configure

```bash
composer require calisero/calisero-php
```

The library reads no environment variable: pass the API key to `SmsClient::create()`. By convention the application
reads it from `CALISERO_API_KEY`. Never hard-code the key, never commit it, use one key per environment.

```php
use Calisero\Sms\SmsClient;

$client = SmsClient::create((string) getenv('CALISERO_API_KEY'));
```

`SmsClient::create()` is fixed: base URL `https://rest.calisero.ro/api/v1`, 30-second timeout (10 to connect). For
other timeouts, your own `AuthProviderInterface` or access to the raw response (`HttpClient::getLastResponse()`),
build `HttpClient` and the services yourself (see "Building the Client Yourself" in `README.md`). The services are
`$client->messages()`, `$client->verifications()`, `$client->optOuts()` and `$client->accounts()`.

The samples below use named arguments (PHP 8.0+). On PHP 7.4 pass the arguments in the constructor's order, with
`null` for the ones you skip.

### Send an SMS

```php
use Calisero\Sms\Dto\CreateMessageRequest;

$response = $client->messages()->create(new CreateMessageRequest(
    recipient: '+40712345678',                    // E.164
    body: 'Your order #1001 has shipped.',        // at most 1600 characters
    // sender: 'MyShop',                          // only a sender approved for the account
    // scheduleAt: '2026-12-24 10:00:00',         // a string, 'Y-m-d H:i:s' in Romania time
    // callbackUrl: 'https://example.com/webhooks/calisero?token=' . $token,
    // shortenUrls: true, visibleBody: '...', validity: 24 (hours)
));

$message = $response->getData();
$message->getId();                                // keep it: it matches the delivery callbacks
$message->getStatus();                            // scheduled, sent...
$message->getParts();
$response->getResponseMeta()->getTraceId();
$response->getResponseMeta()->getDailyRemaining(); // null without a daily limit
```

`scheduleAt` is sent as is: format it yourself in Romania time.

```php
$scheduleAt = (new DateTimeImmutable('+1 hour'))
    ->setTimezone(new DateTimeZone('Europe/Bucharest'))
    ->format('Y-m-d H:i:s');
```

### OTP: send and validate

Calisero generates the code (6 characters, letters and digits, case-insensitive), sends it and checks it: never
generate or store it yourself.

```php
use Calisero\Sms\Dto\CreateVerificationRequest;
use Calisero\Sms\Dto\VerificationCheckRequest;
use Calisero\Sms\Exceptions\ValidationException;

$client->verifications()->create(new CreateVerificationRequest('+40712345678', 'MyShop'));
// or: new CreateVerificationRequest($phone, null, 'Your MyShop code is {code}', 5) (expiresIn: 1 to 10 minutes)

try {
    $verification = $client->verifications()->validate(new VerificationCheckRequest($phone, $input))->getData();
    // $verification->getStatus() === 'verified'
} catch (ValidationException $e) {
    // wrong or expired code, or too many attempts (422): $e->getMessage() says which
}
```

### Read a message's status

```php
$message = $client->messages()->get($messageId)->getData();
$message->getStatus();       // scheduled, sent, delivered, undelivered...
$message->getDeliveredAt();  // null until delivered
```

`$client->messages()->list(2)` lists messages (50 a page); `$client->messages()->delete($id)` cancels a message that
is still scheduled. A message that stays `sent` was not confirmed by the carrier, which does not mean it failed.

### Delivery webhook

The library parses and validates the payload; routing, the token check and the answer are yours. Set `callbackUrl` on
the message with a secret token in it, then:

```php
use Calisero\Sms\Dto\DeliveryWebhookMessage;

if (!hash_equals((string) getenv('CALISERO_WEBHOOK_TOKEN'), (string) ($_GET['token'] ?? ''))) {
    http_response_code(401);
    exit;
}

try {
    $update = DeliveryWebhookMessage::fromJson((string) file_get_contents('php://input'));
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    exit;
}

// $update->getMessageId(), getStatus() ('sent', 'delivered', 'undelivered'), getDeliveredAt(), getPrice(),
// getRemainingBalance(), getDailyRemaining()
$queue->push($update->getMessageId(), $update->getStatus()); // store or enqueue, then answer

http_response_code(200);
echo '{"received":true}';
```

In a framework, pass the decoded body to `DeliveryWebhookMessage::fromArray($data)`.

### Errors and the trace ID

Every error is a `Calisero\Sms\Exceptions\ApiException` (`getMessage()`, `getStatusCode()`, `getTraceId()`,
`getErrorDetails()`), of the subclass of its status:

| Exception | When |
| --- | --- |
| `UnauthorizedException` | 401: missing or invalid API key |
| `ForbiddenException` | 403: not allowed, e.g. deleting a message that is no longer scheduled |
| `NotFoundException` | 404: unknown ID |
| `ValidationException` | 422: `getValidationErrors()` maps each field to its messages |
| `DailyLimitExceededException` | 429 with `daily_limit_exceeded` (`getDailyLimit()`, `getResetsAt()`); extends `RateLimitedException`, so catch it first |
| `RateLimitedException` | 429: request rate limit, wait `getRetryAfter()` seconds |
| `ServerException` | 500, 502, 503, 504 |
| `TransportException` | no answer: connection failed or timed out |

Log `$e->getTraceId()` with every failure and `getResponseMeta()->getTraceId()` with every success. Every answer
carries it as `X-Trace-Id` (an error body also as `trace_id`); the dashboard's Developers → Debug page shows
everything that happened to that request.

### Tests

- Unit tests: let your code receive the service it uses (`MessageService`…), and in tests build that service on a
  mocked `Calisero\Sms\Http\HttpClient` that returns the API's decoded JSON (see "Testing Your Implementation" in
  `README.md`).
- Staging and CI against the real API: use a sandbox API key. It goes through the same validation but sends nothing,
  bills nothing and never counts towards the daily limit; the message is `delivered` at once, callbacks still arrive,
  and a test OTP code shows in the dashboard.

### Rules that matter

- **Phone numbers** in E.164: `+40712345678`, not `0712345678`. Normalise user input before sending.
- **Length and cost**: one part holds 160 GSM-7 characters, or 70 when the text has Romanian diacritics (ă, â, î, ș,
  ț) or emoji (UCS-2). A longer text is split into parts, each billed as one SMS (`getParts()`); `body` takes at most
  1600 characters. When cost matters, a text without diacritics keeps 160-character parts: an option, not a rule.
- **Sender ID**: alphanumeric, at most 11 characters, approved for the account and written exactly as approved;
  otherwise 422 and nothing is sent. Leave `sender` out for the default sender. It cannot receive replies. Never take
  it from user input. OTP codes go out from the shared "Verify" sender and need no sender of your own.
- **Scheduling**: `scheduleAt` is `Y-m-d H:i:s` in Romania time (Europe/Bucharest); an ISO 8601 string is refused.
- **Webhooks**: answer any 2xx within 2 seconds, do the work afterwards. Only a timeout or a failed connection is
  retried (at most 5 attempts); a non-2xx answer is not. The same update can arrive twice and out of order: key your
  processing on `messageId` + `status`, and never move a message back from `delivered`. `callbackUrl` must be a public
  http(s) URL: private, loopback and link-local addresses are refused, and redirects are not followed. Calisero does
  not sign callbacks, hence the token in the URL.
- **429 has two meanings**: the request rate limit (240 requests a minute; retry after `getRetryAfter()`) and the
  daily sending limit (`DailyLimitExceededException`: nothing sent, retrying the same day will not help; it resets at
  midnight, Romania time, `getResetsAt()`). Created messages and verifications report `X-Daily-Limit` /
  `X-Daily-Remaining` in `getResponseMeta()`.
- **No idempotency key**: the API takes none yet. `SmsClient::create()` adds an `Idempotency-Key` header to every
  message and verification it creates, but it does not make a retry safe. The library never retries; do not retry `messages()->create()` blindly
  after a timeout or a `TransportException`: the SMS may have been sent and billed. Check `messages()->list()` first.
- **TLS**: the API accepts TLS 1.2 and 1.3 only (TLS 1.0 and 1.1 are refused from 1 November 2026, with no HTTP
  answer). The library never offers less than TLS 1.2; keep the `https://` base URL, since `http://` is redirected and
  the library does not follow redirects.
- **OTP**: `brand` or a `template` holding `{code}`, neither with links. Each code is a billed SMS that counts towards
  the daily limit: rate-limit resends in your application.

## Working on this library

Read `CONTRIBUTING.md` and `TESTING.md` before changing code.

- **Layout**: `src/SmsClient.php` (entry point), `src/Services/` (one per resource), `src/Dto/` (requests, responses,
  `DeliveryWebhookMessage`, `ResponseMeta`), `src/Exceptions/`, `src/Http/` (cURL transport, `HttpClient` and its error
  mapping). Tests in `tests/Unit`, runnable scripts in `examples/`.
- **API contract**: field names, types and status codes come from the API's OpenAPI document
  (https://docs.calisero.ro/open-api), kept in `resources/openApi/api-v1.json`. `tests/Unit/Contract/OpenApiContractTest`
  checks the library against it: when the API changes, replace the document and see what fails.
- **Checks**:

  ```bash
  composer install
  composer qa        # validate, lint (PHP-CS-Fixer dry run), stan (PHPStan level 9), test: what CI runs
  composer format    # fix the code style
  vendor/bin/phpunit --filter DailyLimit
  ```

- **Conventions**: the library and its tests run on PHP 7.4 to 8.5 and PHPUnit 9.6 to 12. Use PHP 7.4 syntax only
  (no named arguments, `match`, nullsafe operator, constructor promotion or union types) and no PHPUnit attributes or
  docblock annotations (one test method per case). Strict types, full type declarations, PSR-12. No test makes a
  network request: services run on a mocked `HttpClient`.
- **Changelog and version**: record changes in `CHANGELOG.md` (Keep a Changelog); a release sets
  `SmsClient::VERSION`, which a test matches against the latest changelog version.
