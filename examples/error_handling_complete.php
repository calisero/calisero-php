<?php

declare(strict_types=1);

/**
 * Comprehensive error handling examples for all API operations.
 * This example demonstrates how to handle different types of errors
 * that can occur when using the Calisero SMS API.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\ForbiddenException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\Exceptions\RateLimitedException;
use Calisero\Sms\Exceptions\ServerException;
use Calisero\Sms\Exceptions\TransportException;
use Calisero\Sms\Exceptions\UnauthorizedException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

echo "=== Comprehensive Error Handling Examples ===\n\n";

// 1. Authentication Errors (401)
echo "1️⃣ Testing Authentication Errors...\n";

try {
    $request = new CreateMessageRequest('+40742***350', 'Test message');
    SmsClient::create('invalid-token')->messages()->create($request);
} catch (UnauthorizedException $e) {
    echo "✅ Caught UnauthorizedException (expected):\n";
    echo "   💬 Message: {$e->getMessage()}\n";
    echo "   🔢 Status Code: {$e->getStatusCode()}\n";
    echo '   🆔 Request ID: ' . ($e->getRequestId() ?? 'N/A') . "\n";
    echo "   💡 Solution: Check your bearer token and ensure it's valid\n\n";
} catch (ApiException $e) {
    echo "✅ Caught general ApiException:\n";
    echo "   💬 Message: {$e->getMessage()}\n";
    echo '   🔢 Status Code: ' . ($e->getStatusCode() ?? 'N/A') . "\n\n";
}

// 2. Validation Errors (422)
echo "2️⃣ Testing Validation Errors...\n";

try {
    // Invalid phone number format
    $invalidRequest = new CreateMessageRequest('invalid-phone', '');
    SmsClient::create($bearerToken)->messages()->create($invalidRequest);
} catch (ValidationException $e) {
    echo "✅ Caught ValidationException (expected):\n";
    echo "   💬 Message: {$e->getMessage()}\n";
    echo "   🔢 Status Code: {$e->getStatusCode()}\n";
    echo '   🆔 Request ID: ' . ($e->getRequestId() ?? 'N/A') . "\n";

    if ($e->getValidationErrors()) {
        echo "   📝 Validation Details:\n";
        foreach ($e->getValidationErrors() as $field => $errors) {
            echo "     - {$field}: " . implode(', ', $errors) . "\n";
        }
    }
    echo "   💡 Solution: Fix the validation errors and retry\n\n";
} catch (ApiException $e) {
    echo "✅ Caught general ApiException:\n";
    echo "   💬 Message: {$e->getMessage()}\n";
    echo '   🔢 Status Code: ' . ($e->getStatusCode() ?? 'N/A') . "\n\n";
}

// 3. Not Found Errors (404)
echo "3️⃣ Testing Not Found Errors...\n";

try {
    // Try to get a non-existent message
    SmsClient::create($bearerToken)->messages()->get('non-existent-message-id');
} catch (NotFoundException $e) {
    echo "✅ Caught NotFoundException (expected):\n";
    echo "   💬 Message: {$e->getMessage()}\n";
    echo "   🔢 Status Code: {$e->getStatusCode()}\n";
    echo '   🆔 Request ID: ' . ($e->getRequestId() ?? 'N/A') . "\n";
    echo "   💡 Solution: Verify the resource ID exists\n\n";
} catch (ApiException $e) {
    echo "✅ Caught general ApiException:\n";
    echo "   💬 Message: {$e->getMessage()}\n";
    echo '   🔢 Status Code: ' . ($e->getStatusCode() ?? 'N/A') . "\n\n";
}

// 4. Forbidden Errors (403)
echo "4️⃣ Testing Forbidden Errors...\n";

try {
    // This might trigger a forbidden error if the account doesn't have permissions
    // or if trying to delete a message that cannot be deleted
    SmsClient::create($bearerToken)->messages()->delete('some-message-id');
} catch (ForbiddenException $e) {
    echo "✅ Caught ForbiddenException (might occur):\n";
    echo "   💬 Message: {$e->getMessage()}\n";
    echo "   🔢 Status Code: {$e->getStatusCode()}\n";
    echo '   🆔 Request ID: ' . ($e->getRequestId() ?? 'N/A') . "\n";
    echo "   💡 Solution: Check your account permissions or operation restrictions\n\n";
} catch (NotFoundException $e) {
    echo "✅ Caught NotFoundException (expected for non-existent message):\n";
    echo "   💬 Message: {$e->getMessage()}\n\n";
} catch (ApiException $e) {
    echo "✅ Caught general ApiException:\n";
    echo "   💬 Message: {$e->getMessage()}\n";
    echo '   🔢 Status Code: ' . ($e->getStatusCode() ?? 'N/A') . "\n\n";
}

// 5. Rate Limiting Errors (429)
echo "5️⃣ Testing Rate Limiting (429) - Simulation...\n";

try {
    // This is a simulation - actual rate limiting would require many requests
    throw new RateLimitedException(
        'Rate limit exceeded. Try again later.',
        0, // exception code
        null, // previous exception
        429, // status code
        'req_123456789', // request ID
        [], // error details
        60 // retry after seconds
    );
} catch (RateLimitedException $e) {
    echo "✅ Caught RateLimitedException (simulated):\n";
    echo "   💬 Message: {$e->getMessage()}\n";
    echo "   🔢 Status Code: {$e->getStatusCode()}\n";
    echo '   🆔 Request ID: ' . ($e->getRequestId() ?? 'N/A') . "\n";

    // Check for retry after
    $retryAfter = $e->getRetryAfter();
    if ($retryAfter) {
        echo "   ⏰ Retry After: {$retryAfter} seconds\n";
        echo "   💡 Solution: Wait {$retryAfter} seconds before retrying\n";
    } else {
        echo "   💡 Solution: Wait a few moments before retrying\n";
    }
    echo "\n";
}

// 6. Server Errors (5xx)
echo "6️⃣ Testing Server Errors (5xx) - Simulation...\n";

try {
    // This is a simulation
    throw new ServerException(
        'Internal server error occurred',
        0, // exception code
        null, // previous exception
        500, // status code
        'req_123456789' // request ID
    );
} catch (ServerException $e) {
    echo "✅ Caught ServerException (simulated):\n";
    echo "   💬 Message: {$e->getMessage()}\n";
    echo "   🔢 Status Code: {$e->getStatusCode()}\n";
    echo '   🆔 Request ID: ' . ($e->getRequestId() ?? 'N/A') . "\n";
    echo "   💡 Solution: Wait and retry, or contact support if it persists\n\n";
}

// 7. Transport/Network Errors
echo "7️⃣ Testing Transport Errors - Simulation...\n";

try {
    // This is a simulation
    throw new TransportException('Connection timeout occurred');
} catch (TransportException $e) {
    echo "✅ Caught TransportException (simulated):\n";
    echo "   💬 Message: {$e->getMessage()}\n";
    echo "   💡 Solution: Check network connectivity and retry\n\n";
}

// 8. Comprehensive Error Handler Function
echo "8️⃣ Demonstrating Comprehensive Error Handler...\n";

function handleSmsApiError(Throwable $e): void
{
    echo "🔍 Error Analysis:\n";
    echo '   📝 Type: ' . get_class($e) . "\n";
    echo "   💬 Message: {$e->getMessage()}\n";

    if ($e instanceof ApiException) {
        echo '   🔢 HTTP Status: ' . ($e->getStatusCode() ?? 'N/A') . "\n";
        echo '   🆔 Request ID: ' . ($e->getRequestId() ?? 'N/A') . "\n";

        // Specific handling based on error type
        switch (get_class($e)) {
            case UnauthorizedException::class:
                echo "   🔧 Action: Check authentication credentials\n";

                break;

            case ValidationException::class:
                echo "   🔧 Action: Fix validation errors in request data\n";

                /** @var ValidationException $e */
                if ($e->getValidationErrors()) {
                    echo "   📋 Validation Details:\n";
                    foreach ($e->getValidationErrors() as $field => $errors) {
                        echo "     - {$field}: " . implode(', ', $errors) . "\n";
                    }
                }

                break;

            case NotFoundException::class:
                echo "   🔧 Action: Verify resource exists and ID is correct\n";

                break;

            case ForbiddenException::class:
                echo "   🔧 Action: Check account permissions and operation restrictions\n";

                break;

            case RateLimitedException::class:
                echo "   🔧 Action: Implement exponential backoff and retry logic\n";

                /** @var RateLimitedException $e */
                $retryAfter = $e->getRetryAfter();
                if ($retryAfter) {
                    echo "   ⏰ Retry after: {$retryAfter} seconds\n";
                }

                break;

            case ServerException::class:
                echo "   🔧 Action: Wait and retry, contact support if persistent\n";

                break;

            default:
                echo "   🔧 Action: Review API documentation for this error type\n";
        }
    } else {
        echo "   🔧 Action: Check network connectivity and application logic\n";
    }

    echo "   📍 File: {$e->getFile()}:{$e->getLine()}\n";
}

// Example usage of the error handler
try {
    throw new ValidationException(
        'Validation failed',
        0, // exception code
        null, // previous exception
        422, // status code
        'req_example', // request ID
        [], // error details
        ['recipient' => ['The recipient field is required.']] // validation errors
    );
} catch (Throwable $e) {
    handleSmsApiError($e);
}

echo "\n✨ Error handling examples completed!\n";
echo "\n💡 Best Practices:\n";
echo "  1. Always catch specific exception types first, then general ones\n";
echo "  2. Log request IDs for support inquiries\n";
echo "  3. Implement retry logic for rate limits and server errors\n";
echo "  4. Validate input data before making API calls\n";
echo "  5. Handle network timeouts gracefully\n";
echo "  6. Provide meaningful error messages to users\n";
echo "  7. Monitor error rates and patterns in production\n";
