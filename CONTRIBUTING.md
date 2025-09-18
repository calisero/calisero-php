# Contributing to Calisero PHP

Thank you for considering contributing to the Calisero PHP SMS API library! We welcome contributions from the community.

## Development Setup

1. Fork the repository
2. Clone your fork: `git clone https://github.com/your-username/calisero-php.git`
3. Install dependencies: `composer install`
4. Create a new branch: `git checkout -b feature/your-feature-name`

## Code Standards

This project follows strict coding standards:

- **PSR-12** coding style
- **PHPStan level 9** static analysis
- **Full test coverage** for new features
- **Type declarations** for all parameters and return types

### Running Quality Assurance

```bash
# Run all QA checks
composer qa

# Or run individually:
composer lint     # Check code style
composer stan     # Run static analysis
composer test     # Run tests
composer format   # Fix code style issues
```

## Testing

- Write tests for all new functionality
- Ensure existing tests continue to pass
- Use meaningful test names and assertions
- Cover both success and error scenarios

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test -- --coverage-html coverage
```

## Pull Request Process

1. Ensure all QA checks pass (`composer qa`)
2. Update the README.md if needed
3. Update CHANGELOG.md following [Keep a Changelog](https://keepachangelog.com/) format
4. Create a Pull Request with:
   - Clear title and description
   - Reference to any related issues
   - Screenshots/examples if applicable

## Code Style

We use PHP-CS-Fixer with a custom configuration. Key points:

- Strict types declaration
- PSR-12 compliance
- Type hints for all parameters and return types
- Meaningful variable and method names
- Comprehensive DocBlocks

## Commit Messages

Use clear, descriptive commit messages:

```
feat: add support for custom HTTP headers
fix: handle null values in message responses
docs: update README with new examples
test: add integration tests for opt-out service
```

## Reporting Issues

When reporting issues, please include:

- PHP version
- Library version
- Minimal code example reproducing the issue
- Expected vs actual behavior
- Any error messages or stack traces

## Security

If you discover a security vulnerability, please send an email to support@calisero.ro instead of using the issue tracker.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
