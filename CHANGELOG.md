# Changelog

All notable changes to `calisero-php` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
