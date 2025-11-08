# Changelog

All notable changes to `calisero-php` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
