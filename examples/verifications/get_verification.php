<?php

declare(strict_types=1);

/**
 * Get verification details by ID.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

// Replace with an actual verification ID
$verificationId = '019a62f1-66b7-7387-a64f-2742c12a2860';

try {
    echo "=== Get Verification ===\n\n";

    // Get verification using fluent chaining
    $response = SmsClient::create($bearerToken)
        ->verifications()
        ->get($verificationId);

    $verification = $response->getData();

    echo "✅ Verification retrieved successfully!\n";
    echo "🆔 ID: {$verification->getId()}\n";
    echo "📱 Phone: {$verification->getPhone()}\n";
    echo "📊 Status: {$verification->getStatus()}\n";
    echo '✅ Verified at: ' . ($verification->getVerifiedAt() ?? 'Not verified yet') . "\n";
    echo "⏰ Expires at: {$verification->getExpiresAt()}\n";
    echo "🧪 Attempts: {$verification->getAttempts()}\n";
    echo '⌛ Expired: ' . ($verification->isExpired() ? 'Yes' : 'No') . "\n";
} catch (NotFoundException $e) {
    echo "❌ Verification not found: {$e->getMessage()}\n";
    echo "💡 Please check if the verification ID '{$verificationId}' is correct\n";
} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";

    if ($e->getStatusCode()) {
        echo "🔢 Status Code: {$e->getStatusCode()}\n";
    }

    if ($e->getTraceId()) {
        echo "🆔 Trace ID: {$e->getTraceId()}\n";
    }
}
