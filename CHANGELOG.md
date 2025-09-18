# Changelog

All notable changes to `calisero-php` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-09-18

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

### Improved
- Enhanced error handling with specific exception types and detailed error messages
- Better documentation with practical examples for all API operations
- Improved testing coverage with examples syntax validation in CI
- Added comprehensive API surface documentation through examples

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
