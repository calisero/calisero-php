# Testing

The library's unit tests live in `tests/Unit` and run with PHPUnit. They make no
network requests: the services run against a mocked `HttpClient`, and the HTTP
layer against stub transports and real `Request` and `Response` objects.

As of 2.3.1 the suite has **89 tests in 16 test classes**. PHPUnit counts
assertions differently from one version to the next: 502 on PHPUnit 12.5, 444 on
PHPUnit 9.6.

## Running the Tests

```bash
# All unit tests
composer test

# With an HTML coverage report in coverage/ (needs Xdebug or PCOV)
composer test-coverage

# Code style, static analysis and tests, as CI runs them
composer qa

# One test class, or the tests whose names match a pattern
vendor/bin/phpunit tests/Unit/Services/MessageServiceTest.php
vendor/bin/phpunit --filter DailyLimit
```

On PHPUnit 12, `composer test` ends with "OK, but there were issues!" and 6
PHPUnit notices: `HttpClientTest` creates mock objects it sets no expectations on.
The notices are advisory and do not fail the run.

## Supported Versions

CI runs the suite on PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4 and 8.5, with the highest
dependencies on every version and the lowest on 7.4, 8.0 and 8.1. Depending on the
PHP version, Composer installs PHPUnit 9.6, 10.5, 11.5 or 12, so every test must
run on all of them:

- PHP 7.4 syntax only: no named arguments, `match`, nullsafe operator,
  constructor promotion or union types.
- No PHPUnit attributes and no docblock annotations such as `@dataProvider`:
  PHPUnit 9.6 does not read attributes and PHPUnit 12 no longer reads
  annotations. Write one test method per case instead.

## Test Classes

### Services (`tests/Unit/Services`)

| Test class | Tests | Covers |
|---|---|---|
| `MessageServiceTest` | 9 | `create()` with a validity, callback URL and sender, with the minimal request, for a scheduled message and with `shortenUrls`, and the `ResponseMeta` it reads from the answer's headers; `get()`; `list()` on the first and on a later page; `delete()` |
| `VerificationServiceTest` | 5 | `list()`; `create()` and the daily limit headers of its answer; `get()`; `validate()` |
| `OptOutServiceTest` | 10 | `create()` with and without a reason, and with a long one; `get()`; `list()` on the first page, on a later page and with no result; `update()` with and without a reason; `delete()` |
| `AccountServiceTest` | 7 | `get()` for a typical account, a minimal one, one with low credit, an inactive one and one with every contact field filled, and with and without a daily sending limit |

Each test mocks `Calisero\Sms\Http\HttpClient`, expects the call the service
should make (method, path, payload and, for creates, the idempotency flag) and
returns the API's JSON body, decoded. To test what a service reads from the
answer's headers, stub `getLastResponse()` with a real
`Calisero\Sms\Http\Response`.

### HTTP Layer (`tests/Unit/Http`)

| Test class | Tests | Covers |
|---|---|---|
| `HttpClientTest` | 6 | Building GET and POST requests (URL, headers, JSON body) with a mocked transport, request factory and auth provider, and keeping their response; mapping 400, 401, 404 and 422 answers to exceptions |
| `HttpClientResponseHandlingTest` | 12 | Real `Request` and `Response` objects through a stub transport, answered as the API answers: the error message and the trace ID (from the header in any spelling, from the error body, from an older `X-Request-ID` header); an error body that is not JSON; 422s with and without field errors; the request rate limit's 429 against the daily sending limit's; `getLastResponse()` after a success, an error and a transport failure; the `User-Agent` header, with the library, PHP and platform |
| `ResponseTest` | 3 | Header lookup that ignores the case of names, which cURL reports in mixed case over HTTP/1.1 and in lowercase over HTTP/2 |

`BaseHttpClient`, the cURL transport, has no unit tests, since nothing in the suite
makes a real request. Check changes to it by hand, for instance against a local
`php -S` server.

### DTOs (`tests/Unit/Dto`)

| Test class | Tests | Covers |
|---|---|---|
| `CreateMessageRequestTest` | 6 | Getters and `toArray()` for a minimal and a full request, and `shorten_urls`, sent only when set (`false` included) |
| `MessageTest` | 4 | `fromArray()` with every field, without the optional ones, with shortened URLs and with an empty list of them |
| `ShortenedLinkTest` | 2 | `fromArray()` for a clicked link and for one never clicked |
| `ResponseMetaTest` | 5 | Reading the trace ID, rate limit and daily limit headers; names in any case; an account without a daily limit; values that are not numbers; no response at all |
| `CreateMessageResponseTest` | 2 | `withResponseMeta()` returns a copy; `fromArray()` of both create responses keeps the one-parameter signature that subclasses override |
| `DeliveryWebhookMessageTest` | 8 | `fromJson()` and `fromArray()` with a full payload, a minimal one, one that predates the daily limit and a price sent as a string; refusing invalid JSON, JSON that is not an object, a missing `messageId` and a missing price |

### Client and Helpers

| Test class | Tests | Covers |
|---|---|---|
| `SmsClientTest` (`tests/Unit`) | 7 | `SmsClient::create()` and its services: the same instance on every call, distinct services, independent clients; `SmsClient::VERSION` matching the latest version in CHANGELOG.md |
| `BearerTokenAuthProviderTest` (`tests/Unit/Auth`) | 1 | The provider returns the token it was given |
| `UuidIdempotencyKeyProviderTest` (`tests/Unit/IdempotencyKey`) | 2 | Keys are UUIDs and differ from one call to the next |

## Writing Tests

- Build fixtures from what the API really sends: the examples of the Calisero API
  documentation and the field names the DTOs in `src/Dto` read.
- Use `createMock()` when the test sets expectations, and a stub (`createStub()`
  or an anonymous class) when it only needs canned answers: PHPUnit 12 reports a
  notice for each mock that gets no expectations.
- Cover the error paths as well as the successful ones: each exception and the
  headers and body fields it reads.
- Run `composer qa` before opening a pull request.
