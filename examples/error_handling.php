<?php

declare(strict_types=1);

/**
 * Comprehensive error handling example.
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
use Calisero\Sms\Sms;

// Replace with your actual bearer token
$bearerToken = 'your-bearer-token-here';

try {
    // Create the SMS client
    $client = Sms::client($bearerToken);

    // Try to send a message that might fail
    $request = new CreateMessageRequest(
        'invalid-phone-number',  // This will cause a validation error
        'Test message'
    );

    $response = $client->messages()->create($request);
    echo '✅ Message sent: ' . $response->getData()->getId() . PHP_EOL;
} catch (UnauthorizedException $e) {
    // 401 - Authentication failed
    echo '🔐 Authentication Error (401)' . PHP_EOL;
    echo '❌ ' . $e->getMessage() . PHP_EOL;
    echo '💡 Check your bearer token' . PHP_EOL;

    if ($e->getRequestId()) {
        echo '🆔 Request ID: ' . $e->getRequestId() . PHP_EOL;
    }
} catch (ForbiddenException $e) {
    // 403 - Access forbidden
    echo '🚫 Access Forbidden (403)' . PHP_EOL;
    echo '❌ ' . $e->getMessage() . PHP_EOL;
    echo '💡 Check your account permissions' . PHP_EOL;
} catch (NotFoundException $e) {
    // 404 - Resource not found
    echo '🔍 Not Found (404)' . PHP_EOL;
    echo '❌ ' . $e->getMessage() . PHP_EOL;
    echo '💡 Check that the resource ID exists' . PHP_EOL;
} catch (ValidationException $e) {
    // 422 - Validation errors
    echo '📝 Validation Error (422)' . PHP_EOL;
    echo '❌ ' . $e->getMessage() . PHP_EOL;
    echo '💡 Check the following field errors:' . PHP_EOL;

    foreach ($e->getValidationErrors() as $field => $errors) {
        echo "  🔸 {$field}:" . PHP_EOL;
        foreach ($errors as $error) {
            echo "    - {$error}" . PHP_EOL;
        }
    }
} catch (RateLimitedException $e) {
    // 429 - Rate limit exceeded
    echo '⏰ Rate Limited (429)' . PHP_EOL;
    echo '❌ ' . $e->getMessage() . PHP_EOL;

    if ($retryAfter = $e->getRetryAfter()) {
        echo "💡 Retry after: {$retryAfter} seconds" . PHP_EOL;
        echo '🕐 Next attempt at: ' . date('Y-m-d H:i:s', time() + $retryAfter) . PHP_EOL;
    } else {
        echo '💡 Please wait before making more requests' . PHP_EOL;
    }
} catch (ServerException $e) {
    // 5xx - Server errors
    echo '🔥 Server Error (' . $e->getStatusCode() . ')' . PHP_EOL;
    echo '❌ ' . $e->getMessage() . PHP_EOL;
    echo '💡 This is a temporary issue, please try again later' . PHP_EOL;

    if ($e->getRequestId()) {
        echo '🆔 Request ID (for support): ' . $e->getRequestId() . PHP_EOL;
    }
} catch (TransportException $e) {
    // Network/transport errors
    echo '🌐 Network Error' . PHP_EOL;
    echo '❌ ' . $e->getMessage() . PHP_EOL;
    echo '💡 Check your internet connection and try again' . PHP_EOL;
} catch (ApiException $e) {
    // Any other API error
    echo '⚠️ API Error (' . ($e->getStatusCode() ?: 'Unknown') . ')' . PHP_EOL;
    echo '❌ ' . $e->getMessage() . PHP_EOL;

    if ($e->getStatusCode()) {
        echo '🔢 Status Code: ' . $e->getStatusCode() . PHP_EOL;
    }

    if ($e->getRequestId()) {
        echo '🆔 Request ID: ' . $e->getRequestId() . PHP_EOL;
    }

    if ($e->getErrorDetails()) {
        echo '📋 Error Details: ' . json_encode($e->getErrorDetails(), JSON_PRETTY_PRINT) . PHP_EOL;
    }
} catch (Exception $e) {
    // Any other unexpected error
    echo '💥 Unexpected Error' . PHP_EOL;
    echo '❌ ' . $e->getMessage() . PHP_EOL;
    echo '💡 Please report this issue to support@calisero.ro' . PHP_EOL;
}

echo PHP_EOL . '🏁 Done!' . PHP_EOL;
