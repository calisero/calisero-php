<?php

declare(strict_types=1);

/**
 * Validate an OTP code for a phone number.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Dto\VerificationCheckRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

// Example inputs
$phone = '+40742***350';
$code = 'ABC123'; // 6-char code

try {
    echo "=== Validate Verification (OTP) ===\n\n";

    $request = new VerificationCheckRequest($phone, $code);

    // Validate code using fluent chaining
    $response = SmsClient::create($bearerToken)
        ->verifications()
        ->validate($request);

    $verification = $response->getData();

    echo "✅ Verification validated successfully!\n";
    echo "📱 Phone: {$verification->getPhone()}\n";
    echo "📊 Status: {$verification->getStatus()}\n";
    echo '✅ Verified at: ' . ($verification->getVerifiedAt() ?? 'Just verified / already verified') . "\n";
    echo "🧪 Attempts used: {$verification->getAttempts()}\n";
} catch (ValidationException $e) {
    echo "❌ Validation failed: {$e->getMessage()}\n";

    $errors = method_exists($e, 'getValidationErrors') ? $e->getValidationErrors() : null;
    if (is_array($errors) && !empty($errors)) {
        echo "📝 Validation details (from API):\n";
        foreach ($errors as $field => $fieldErrors) {
            echo "  - {$field}: " . implode(', ', (array) $fieldErrors) . "\n";
        }
    }
} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";

    if ($e->getStatusCode()) {
        echo "🔢 Status Code: {$e->getStatusCode()}\n";
    }

    if ($e->getTraceId()) {
        echo "🆔 Trace ID: {$e->getTraceId()}\n";
    }
}
