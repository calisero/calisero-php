# Changelog

All notable changes to `calisero-php` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.3.1] - 2026-10-06

### Changed
- The `User-Agent` header names PHP and the platform along with the library version, the way the other Calisero libraries do: `Calisero-SMS-PHP/2.3.1 (PHP 8.5.3; linux x86_64)` instead of `Calisero-SMS-PHP/2.3.0`. The machine is left out where `php_uname()` is disabled, as some shared hosts do.

### Documentation
- `README.md` describes the `User-Agent` header under Advanced Configuration.

## [2.3.0] - 2026-10-05

Support for version 1.0.14 of the Calisero API. Only constructors gained parameters, all optional and last; no other public method changed its signature. Behavior changes are listed under Changed and Fixed.

### Added
- **URL shortening.** `CreateMessageRequest` takes a new optional `shortenUrls` argument (sent as `shorten_urls`): Calisero replaces the `http://` and `https://` links of the body with short ones before sending. `Message::getShortenedUrls()` returns them, on create, get and list, as the new `ShortenedLink` DTO: `getOriginalLink()`, `getShortenedLink()`, `getClickCount()`, `getLastClick()`, `getCreatedAt()`.
- **Daily sending limit.**
  - `Account::getDailyLimit()`, `getDailyRemaining()` and `getSentToday()` (`daily_limit`, `daily_remaining`, `sent_today`).
  - New `ResponseMeta` DTO, returned by `CreateMessageResponse::getResponseMeta()` and `CreateVerificationResponse::getResponseMeta()`: the `X-Daily-Limit` and `X-Daily-Remaining` headers of the answer, along with `X-RateLimit-Limit`, `X-RateLimit-Remaining` and `X-Trace-Id`. Both responses also gained `withResponseMeta()`; their `fromArray()` keeps its signature, so subclasses that override it keep working.
  - New `DailyLimitExceededException`, thrown for the `429` whose body carries the code `daily_limit_exceeded`: `getDailyLimit()`, `getDailyRemaining()`, `getResetsAt()`. It extends `RateLimitedException`, so existing `catch (RateLimitedException $e)` blocks keep catching it.
- **Request rate limit details.** `RateLimitedException::getRateLimitLimit()`, `getRateLimitRemaining()` and `getRateLimitReset()`, from the `X-RateLimit-*` headers.
- **Trace ID.** `ApiException::getTraceId()`: the `X-Trace-Id` header of the answer, or the `trace_id` of the error body. `ResponseMeta::getTraceId()` gives it for successful creates.
- **Delivery status webhooks.** New `DeliveryWebhookMessage` DTO whose `fromJson()` and `fromArray()` read and validate the callback payload, the new `dailyLimit`, `dailyRemaining` and `sentToday` fields included, and throw `\InvalidArgumentException` on a malformed one.
- `HttpClient::getLastResponse()`: the raw response to the last request, headers included. The services use it to fill `ResponseMeta`; it is reachable only when you build the `HttpClient` and the services yourself (see "Building the Client Yourself" in the README), since `SmsClient::create()` does not expose its `HttpClient`.
- `SmsClient::VERSION`, kept in step with this changelog by a test.
- Examples: `examples/webhooks/delivery_webhook.php`; URL shortening in `send_advanced_sms.php`; the daily sending limit in `send_bulk_sms.php` (which now stops once the limit is used up), `check_balance.php`, `get_account.php`, `create_verification.php` and `error_handling_complete.php`.
- Unit tests for all of the above, plus `HttpClient` tests that run real `Request` and `Response` objects through the API's actual error bodies and headers: 89 tests, up from 47.

### Changed
- The `User-Agent` header names the library version (`Calisero-SMS-PHP/2.3.0`) instead of the fixed `Calisero-SMS-PHP/1.0`.
- `ApiException::getErrorDetails()` holds the decoded error body for every status, not only for 400, 422 and 429.
- `ValidationException::getValidationErrors()` is empty when a `422` body carries no field errors; it used to return the whole body, which now includes `trace_id`. Every `422` the API sends today has field errors (`errors`), so this only guards against bodies that come from elsewhere, such as a proxy.
- The examples print the trace ID (`getTraceId()`) instead of the request ID.

### Fixed
- **Exception messages were always generic.** The client read the error description from `error.message`, but the API sends it as `message`, so every exception said `HTTP error 404` and the like. Exceptions now carry the API's message (`Resource not found!`, `Too Many Attempts.`, the daily limit explanation…); `error.message` is still read as a fallback.
- **`getRequestId()` was always `null`.** It read an `X-Request-ID` header the API never sends. It now returns the trace ID, like `getTraceId()`.
- **Response headers were missed over HTTP/1.1.** `Response::getHeader()` looked names up in lowercase while `BaseHttpClient` keeps the server's spelling, so over HTTP/1.1 `Retry-After` was never found and `getRetryAfter()` returned `null`. It only worked over HTTP/2, where cURL reports names in lowercase. Header names now match case-insensitively.
- **A PHP 8.5 deprecation notice on every request.** `BaseHttpClient` called `curl_close()`, which PHP 8.5 deprecates (it has done nothing since PHP 8.0). It is now called on PHP 7.4 only. The unit tests never reach `BaseHttpClient`, which is why the 2.2.0 test run on PHP 8.5 reported no deprecations.

### Documentation
- `README.md`: new "Shorten URLs", "Delivery Status Webhooks", "Daily Sending Limit" and "Rate Limits & Trace ID" sections; the Error Handling section now covers `DailyLimitExceededException` and the trace ID and lists every exception with its status; the account and verification snippets show the daily limit.
- `examples/README.md` lists the webhook example and the daily limit refusal.
- `TESTING.md` rewritten to describe the actual suite: 89 tests in 16 classes, what each class covers, how the tests mock the HTTP layer, and the PHP and PHPUnit versions every test must run on. It still described the `Sms` factory and the stream factory removed in 2.0.0, and 114 tests that did not exist.
- `README.md`: the "Custom Authentication Provider" and "Custom Idempotency Key Provider" sections called `new SmsClient(...)`, whose constructor is private; a new "Building the Client Yourself" section shows how to build the `HttpClient` and the services instead. The "Testing Your Implementation" snippet, which did not parse, now tests a `MessageService` built on a mocked `HttpClient`.

### Notes
- Upgrading needs no code change. To tell the two kinds of `429` apart, catch `DailyLimitExceededException` before `RateLimitedException`.
- `ResponseMeta` is only available on `messages()->create()` and `verifications()->create()`, the two calls that report the daily limit.

## [2.2.0] - 2026-09-12

### Added
- **PHP 8.5 support.** PHP 8.5 is now part of the CI test matrix alongside 7.4, 8.0, 8.1, 8.2, 8.3 and 8.4. The library source needed no changes: the full test suite, PHPStan level 9 and PHP-CS-Fixer all run clean on PHP 8.5 with no deprecation notices.

### Changed
- Widened the dev tool constraints so every supported PHP version resolves to a toolchain that runs on it:
  - `phpunit/phpunit` from `^9.6 || ^10.0` to `^9.6 || ^10.5 || ^11.5 || ^12.0`
  - `friendsofphp/php-cs-fixer` from `^3.59` to `^3.75`
- The CI "highest dependencies" leg now always runs `composer update` instead of `composer install` on PHP 8.1+. A single lock file cannot satisfy PHP 7.4 and PHP 8.5 at the same time, and `composer.lock` is not committed to this repository, so `composer install` had no lock file to install from.
- `phpunit.xml.dist` migrated to the PHPUnit 10.5 schema (`<source>` instead of `<coverage><include>`, `cacheDirectory` instead of `cacheResultFile`). PHPUnit 11 and 12 reject the old elements outright.
- The PHP 7.4 and 8.0 CI legs now run `composer test` rather than `composer test-coverage`, since PHPUnit 9.6 does not understand the new `<source>` element and would have no coverage filter. Coverage is still collected and uploaded from PHP 8.1 upward.

### Fixed
- **Half the test suite was silently not running.** `phpunit.xml.dist` declared two overlapping suites — `default` pointing at `tests` and `unit` pointing at `tests/Unit`, which `tests` already contains. PHPUnit 10 executed both, so the reported "94 tests" was the 47 real tests counted twice; PHPUnit 12 refuses the overlap and skipped the second suite entirely. The redundant `default` suite has been removed, leaving a single `unit` suite that runs all 47 tests once.

### Notes
- `phpstan/phpstan` stays on `^1.12`, which runs correctly on PHP 8.5. PHPStan 2 infers `mixed` out of `json_decode()` more precisely and reports 27 pre-existing type-safety findings in `src/Http/HttpClient.php` and the DTO `fromArray()` methods. Those are unrelated to PHP 8.5 and are left for a separate change.
- PHPUnit 12 emits 6 advisory notices suggesting `createStub()` over `createMock()` in `HttpClientTest`. The suggested fix relies on attributes that do not exist in PHPUnit 9.6, so it is deferred while PHP 7.4 is supported.
- The PHP-CS-Fixer rule set is still spelled `@PHP74Migration`. Recent 3.9x releases renamed it to `@PHP7x4Migration` and deprecate the old name, but the new spelling does not exist in the versions the `--prefer-lowest` CI leg installs, so the old name is kept until the floor is raised.

## [2.1.1] - 2025-11-09

### Documentation
- Updated `README.md` to include a new “Verifications (OTP)” section in the API Reference with four concise, copy‑pasteable snippets (create/get/list/validate).
- Updated `examples/README.md` to add the Verifications examples and the directory tree entries.

## [2.1.0] - 2025-11-08

### Added
- Verifications API support:
  - `VerificationService` with methods: `list`, `create`, `get`, `validate`
  - New DTOs: `Verification`, `PaginatedVerifications`, `CreateVerificationRequest`, `CreateVerificationResponse`, `GetVerificationResponse`, `VerificationCheckRequest`
  - `SmsClient::verifications()` accessor
- Examples for verifications:
  - `examples/verifications/create_verification.php`
  - `examples/verifications/get_verification.php`
  - `examples/verifications/list_verifications.php`
  - `examples/verifications/validate_verification.php`
- Unit tests for `VerificationService` covering list/create/get/validate

### Changed
- Updated README with new "Verification Examples (OTP)" section and usage

## [2.0.1] - 2025-09-19

### Fixed
- **HTTP status code mapping**: Corrected incorrect 422 status code handling in HttpClient
  - Fixed `422 Unprocessable Entity` responses that were being incorrectly mapped to `ApiException`
  - Now properly maps `422` to `ValidationException` as intended for validation errors
- **OptOut examples**: Fixed critical issues in all opt-out management examples
  - Fixed undefined `$optOutId` variable in `get_optout.php`
  - Removed exposed API key security issue in `update_optout.php`
  - Fixed `delete_optout.php` to use opt-out ID instead of incorrect phone number parameter
  - Updated all opt-out examples to follow consistent ID-based API patterns
- **ValidationException**: Enhanced error handling to properly extract field-specific validation errors
  - Fixed parameter separation between general errors and validation-specific field errors
  - Improved error message extraction from API responses

### Improved
- **Error handling**: Better validation error processing with proper field-level error extraction
- **Example consistency**: All opt-out examples now follow the same ID-based pattern for better developer experience
- **Security**: Removed hardcoded API keys from example files

## [2.0.0] - 2025-09-19

### Added
- **Minimal architecture**: Completely standalone SMS library with zero external dependencies
- **Native cURL implementation**: Built-in HTTP client using only native PHP cURL extension
- **String-based HTTP bodies**: Simplified request/response handling without stream abstractions
- **Fluent API chaining**: Clean, readable method chaining (`SmsClient::create()->messages()->create()`)
- **Factory methods**: 
  - `SmsClient::create()` - Create SMS client with bearer token and optional configuration
- **Cross-version PHP support**: Enhanced compatibility configuration for PHP 7.4-8.4
- **Proper validation error handling**: Separated general errors from field-specific validation errors

### Changed
- **BREAKING**: Removed all external dependencies (Guzzle, PSR interfaces, stream abstractions)
- **BREAKING**: Removed wrapper classes (`Sms` class) - use `SmsClient::create()` directly
- **BREAKING**: HTTP status code mapping corrected:
  - `400 Bad Request` → `ApiException` (was incorrectly mapped to ValidationException)
  - `422 Unprocessable Entity` → `ValidationException` (proper validation error status)
- **Architecture**: Completely rewritten for minimal footprint and maximum simplicity
- **Dependencies**: Now requires only `php ^7.4||^8.0`, `ext-json`, and `ext-curl`
- **API style**: All examples updated to use fluent method chaining for better readability
- **Test compatibility**: Updated test files to use PHPDoc annotations instead of typed properties for broader PHP 7.4+ compatibility

### Improved
- **Zero dependencies**: No more version conflicts or complex dependency trees
- **Instant installation**: Works immediately with any PHP 7.4+ environment
- **Smaller footprint**: Significantly reduced library size and complexity
- **Better readability**: Fluent chaining makes code more concise and easier to understand
- **Universal compatibility**: Works with any PHP setup without external requirements
- **Validation error handling**: `ValidationException` now properly extracts and exposes field-specific validation errors via `getValidationErrors()` method
- **PHPStan compatibility**: Added `treatPhpDocTypesAsCertain: false` for cross-version type checking
- **OptOut examples**: Fixed all undefined variables and incorrect API usage patterns

### Removed
- **BREAKING**: All PSR interfaces and stream abstractions (unnecessary complexity)
- **BREAKING**: HTTP client discovery system (replaced with simple native cURL)
- **BREAKING**: External HTTP client support (Guzzle, Symfony, HTTPlug)
- **BREAKING**: Stream factories and PSR-17 implementations
- **BREAKING**: `Sms` wrapper class (use `SmsClient::create()` instead)

### Fixed
- **Dependency conflicts**: Eliminated by removing all external dependencies
- **Installation complexity**: Now works out-of-the-box with zero configuration
- **Memory overhead**: Reduced by removing abstraction layers
- **Import statements**: Fixed missing use statements for HTTP interfaces in HttpClient class
- **HTTP status codes**: Corrected 400/422 status code mapping to follow REST API standards
- **Validation errors**: Fixed ValidationException to properly separate error details from validation-specific field errors
- **PHP compatibility**: Fixed cURL initialization check for compatibility across PHP 7.4-8.4
- **OptOut examples**: 
  - Fixed undefined `$optOutId` variable in `get_optout.php`
  - Removed exposed API key from `update_optout.php`
  - Fixed `delete_optout.php` to use opt-out ID instead of phone number
  - Updated all examples to follow consistent ID-based API patterns
- **Cross-version compatibility**: Enhanced PHPStan configuration and cURL handling for PHP 7.4-8.4

### Technical Details
- Implemented native cURL-based HTTP client with authentication and error handling
- Simplified request/response handling using string bodies instead of streams
- Updated all examples to demonstrate fluent method chaining patterns
- Maintained 100% backward compatibility for core API methods
- Enhanced test suite: 86 tests, 528 assertions with comprehensive coverage
- Added proper HTTP status code semantics following REST API standards
- Implemented smart validation error extraction supporting multiple API response formats
- Added cross-version PHP compatibility measures for type checking and cURL handling

## [1.0.3] - 2025-09-18

### Added
- Complete API key management guide with step-by-step instructions for obtaining keys from Calisero dashboard
- Copy-paste ready code examples with complete use statements and `require_once` declarations
- Standalone PHP examples that can be run immediately without modification
- Enhanced Quick Start section with environment configuration best practices
- Comprehensive Common Use Cases section covering OTP/2FA, order notifications, and marketing campaigns with GDPR compliance
- Detailed API Reference with complete, runnable examples for all endpoints
- Advanced Configuration examples including custom HTTP clients and idempotency providers
- Testing section with mock client examples for unit testing user implementations
- Enhanced error handling documentation with all exception types and practical usage patterns

### Improved
- All README code examples are now immediately copy-paste runnable
- Added complete PHP opening tags (`<?php`) and autoloader includes to all examples
- Enhanced developer onboarding experience with clear, standalone examples
- Better documentation structure with comprehensive use statements throughout
- Improved code readability and accessibility for new developers

### Changed
- README examples now include full context and imports for better developer experience
- Updated all code snippets to be standalone and immediately executable
- Enhanced documentation formatting and organization for better readability

### Fixed
- HttpClient test suite compatibility issues with PHPUnit 10.5 and mock object handling
- Test failures related to PSR-7 response body handling and exception interface mocking
- Type compatibility issues in HTTP client tests for cross-version PHP support

## [1.0.2] - 2025-09-18

### Added
- Comprehensive unit test suite covering all API endpoints (114 tests, 552 assertions)
- Complete test coverage for MessageService (create, get, list, delete operations)
- Complete test coverage for OptOutService (CRUD operations for GDPR compliance)
- Complete test coverage for AccountService (account information retrieval)
- Unit tests for SmsClient and factory methods with various configurations
- Test documentation in TESTING.md with detailed coverage overview

### Changed
- PHPUnit configuration updated for cross-version compatibility (9.6 and 10.5)

### Fixed
- PHP CS Fixer cross-version compatibility issues with native_function_invocation rule
- Resolved code formatting inconsistencies across PHP 7.4-8.4 environments
- PHP 7.4 compatibility issues with intersection types in tests (converted to PHPDoc annotations)
- PHP 7.4 compatibility issues with `mixed` type hints in closures (removed for broad compatibility)
- PHPUnit configuration compatibility between versions 9.6 and 10.5
- GitHub CI/CD pipeline failures due to environment differences

## [1.0.1] - 2025-09-18

### Added
- Comprehensive examples organized by functionality
  - **Messages examples**: Basic SMS, advanced SMS with scheduling/callbacks, bulk SMS, message retrieval, listing with pagination, and message deletion
  - **OptOut examples**: Create, get, list, update, and delete opt-outs for GDPR compliance
  - **Account examples**: Get account information and check balance with analysis
  - **Error handling examples**: Comprehensive error handling for all exception types
- Well-organized examples directory structure (`examples/messages/`, `examples/optouts/`, `examples/account/`)
- Example README with quick start guide, best practices, and security guidelines
- Masked phone number format (`+40742***350`) in all examples for security
- Real-world use cases: OTP messages, marketing campaigns, security alerts, GDPR compliance
- Rate limiting examples for bulk operations
- Pagination handling examples
- Balance analysis and message capacity estimation examples

### Changed
- Simplified architecture by making Guzzle HTTP client a required dependency instead of optional
- Streamlined dependency management for better developer experience
- Updated all documentation with comprehensive examples and usage patterns

### Fixed
- Resolved PSR-7 v2.0 compatibility issues by requiring Guzzle directly
- Fixed PHPStan type errors in HttpClient class
- Corrected GitHub Actions workflow for better CI/CD reliability
- Code formatting issues fixed with PHP-CS-Fixer

### Improved
- Enhanced error handling with specific exception types and detailed error messages
- Better documentation with practical examples for all API operations
- Improved testing coverage with examples syntax validation in CI
- Added comprehensive API surface documentation through examples
- Code styling and formatting in examples for readability

## [1.0.0] - 2025-09-18

### Added
- Initial stable release
- Core SMS API functionality (send, get, list, delete messages)
- OptOut management for GDPR compliance
- Account information retrieval
- Comprehensive error handling with specific exception types
- PSR-18 HTTP client support with Guzzle adapter
- Bearer token authentication
- Retry logic with exponential backoff
- Rate limiting support
- Request ID tracking for debugging
- PHP 7.4+ compatibility
- Comprehensive test suite with PHPUnit
- Static analysis with PHPStan level 9
- Code formatting with PHP-CS-Fixer
- GitHub Actions CI/CD pipeline
