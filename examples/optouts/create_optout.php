<?php

declare(strict_types=1);

/**
 * Create an opt-out for a phone number.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Dto\CreateOptOutRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

try {
    echo "=== Create OptOut ===\n\n";

    // Create an opt-out for a phone number
    $request = new CreateOptOutRequest(
        '+40742***350',
        'Customer requested to stop receiving marketing messages'
    );

    $response = SmsClient::create($bearerToken)->optOuts()->create($request);
    $optOut = $response->getData();

    echo "✅ Opt-out created successfully!\n";
    echo "🆔 OptOut ID: {$optOut->getId()}\n";
    echo "📱 Phone Number: {$optOut->getPhone()}\n";
    echo '📝 Reason: ' . ($optOut->getReason() ?? 'Not specified') . "\n";
    echo "⏰ Created: {$optOut->getCreatedAt()}\n";
    echo "🔄 Updated: {$optOut->getUpdatedAt()}\n";

    echo "\n💡 This phone number will no longer receive SMS messages\n";
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

    // Handle specific cases
    if ($e->getStatusCode() === 409) {
        echo "💡 This phone number may already be opted out\n";
    }
}
