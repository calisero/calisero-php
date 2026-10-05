<?php

declare(strict_types=1);

/**
 * Start a phone verification (send OTP code).
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Dto\CreateVerificationRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\DailyLimitExceededException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

try {
    echo "=== Create Verification (OTP) ===\n\n";

    // Create a verification for a phone number
    $request = new CreateVerificationRequest(
        '+40742***350', // phone
        'Calisero'      // brand (optional if custom template provided)
    );

    // Send verification using fluent chaining
    $response = SmsClient::create($bearerToken)
        ->verifications()
        ->create($request);

    $verification = $response->getData();

    echo "✅ Verification created successfully!\n";
    echo "🆔 ID: {$verification->getId()}\n";
    echo "📱 Phone: {$verification->getPhone()}\n";
    echo '🏷️ Brand: ' . ($verification->getBrand() ?? 'N/A') . "\n";
    echo "📊 Status: {$verification->getStatus()}\n";
    echo "⏰ Expires at: {$verification->getExpiresAt()}\n";
    echo "🧪 Attempts: {$verification->getAttempts()}\n";

    // The code's SMS counts towards the account's daily sending limit
    $dailyRemaining = $response->getResponseMeta()->getDailyRemaining();
    if ($dailyRemaining !== null) {
        echo "📊 Daily limit: {$dailyRemaining} messages left today\n";
    }
} catch (DailyLimitExceededException $e) {
    echo "❌ Daily sending limit reached, no code was sent: {$e->getMessage()}\n";
    echo '⏰ Sending resumes at: ' . ($e->getResetsAt() ?? 'midnight, Romania time') . "\n";
} catch (ValidationException $e) {
    echo "❌ Validation error: {$e->getMessage()}\n";

    if ($e->getValidationErrors()) {
        echo "📝 Validation details:\n";
        foreach ($e->getValidationErrors() as $field => $errors) {
            echo "  - {$field}: " . implode(', ', $errors) . "\n";
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
