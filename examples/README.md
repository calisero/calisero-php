# SMS API Examples

This directory contains comprehensive examples for using the Calisero SMS API PHP library. The examples are organized by functionality and demonstrate all available operations with proper error handling.

## Directory Structure

```
examples/
├── messages/               # SMS message operations
│   ├── send_simple_sms.php        # Send a basic SMS message
│   ├── send_advanced_sms.php      # Send SMS with all options (scheduling, callbacks, etc.)
│   ├── send_bulk_sms.php          # Send messages to multiple recipients
│   ├── get_sms.php                # Retrieve message details
│   ├── list_sms.php               # List messages with pagination
│   └── delete_sms.php             # Delete/cancel scheduled messages
├── optouts/                # Opt-out management
│   ├── create_optout.php          # Create opt-out for phone number
│   ├── get_optout.php             # Check opt-out status
│   ├── list_optouts.php           # List all opt-outs with pagination
│   ├── update_optout.php          # Update opt-out reason/details
│   └── delete_optout.php          # Remove opt-out (re-enable SMS)
├── account/                # Account information
│   ├── get_account.php            # Get account details
│   └── check_balance.php          # Check account balance and status
└── error_handling_complete.php    # Comprehensive error handling examples
```

## Quick Start

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Set your credentials:**
   Edit any example file and replace:
   - `'your-api-key-here'` with your actual API token
   - `'+40742***350'` with actual phone numbers (keeping the masked format for security)
   - `'acc_1234567890'` with your actual account ID (for account examples)

3. **Run an example:**
   ```bash
   php examples/messages/send_simple_sms.php
   ```

## Phone Number Format

All examples use masked phone numbers in the format `+40742***350` for security reasons. When using these examples:

- Replace the masked part (`***`) with actual digits
- Ensure phone numbers are in international E.164 format (e.g., `+40742123350`)
- Use valid phone numbers that can receive SMS messages

## Authentication

All examples require a valid bearer token. You can obtain this from your Calisero SMS API dashboard. Replace `'your-api-key-here'` in each example with your actual token.

## Error Handling

Each example includes proper error handling for common scenarios:

- **Authentication errors** (401) - Invalid or expired tokens
- **Validation errors** (422) - Invalid request data
- **Not found errors** (404) - Non-existent resources
- **Rate limiting** (429) - Too many requests
- **Server errors** (5xx) - API server issues

For comprehensive error handling patterns, see `error_handling_complete.php`.

## Message Examples

### Basic Operations
- **send_simple_sms.php** - Send a basic text message
- **send_advanced_sms.php** - Use scheduling, callbacks, custom sender, etc.
- **get_sms.php** - Retrieve message status and details
- **list_sms.php** - Browse message history with pagination

### Advanced Operations
- **send_bulk_sms.php** - Send to multiple recipients with rate limiting
- **delete_sms.php** - Cancel scheduled messages

## OptOut Examples

### GDPR Compliance
- **create_optout.php** - Add phone numbers to opt-out list
- **get_optout.php** - Check if a number is opted out
- **list_optouts.php** - View all opted-out numbers
- **update_optout.php** - Update opt-out reasons
- **delete_optout.php** - Remove from opt-out list (re-enable SMS)

## Account Examples

### Account Management
- **get_account.php** - View account details and contact information
- **check_balance.php** - Monitor credit balance and estimate message capacity

## Best Practices Demonstrated

1. **Proper Error Handling** - All examples catch and handle specific exceptions
2. **Request ID Logging** - Store request IDs for support inquiries
3. **Rate Limiting** - Implement delays for bulk operations
4. **Input Validation** - Validate data before API calls
5. **Secure Logging** - Use visible body parameter for sensitive content
6. **Pagination** - Handle paginated responses correctly
7. **Status Monitoring** - Check message delivery status

## Testing

All examples are validated for syntax in the CI pipeline:

```bash
# Check syntax of all examples
for example in examples/**/*.php; do
    php -l "$example"
done
```

## Support

If you encounter issues with these examples:

1. Check your authentication credentials
2. Verify phone number formats
3. Review the error messages and request IDs
4. Consult the API documentation
5. Contact support with request IDs if needed

## Contributing

When adding new examples:

1. Follow the existing directory structure
2. Include comprehensive error handling
3. Use masked phone numbers for security
4. Add descriptive comments
5. Test syntax with `php -l`
6. Update this README if adding new categories
