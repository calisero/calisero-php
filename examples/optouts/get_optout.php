<?php

declare(strict_types=1);

/**
 * Get opt-out details for a phone number.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

// Replace with the opt-out ID to retrieve
$optOutId = 'opt_1234567890abcdef';

try {
    echo "=== Get OptOut ===\n\n";

    // Fetch the opt-out by ID
    $response = SmsClient::create($bearerToken)->optOuts()->get($optOutId);
    $optOut = $response->getData();

    echo "✅ Opt-out found!\n";
    echo "🆔 OptOut ID: {$optOut->getId()}\n";
    echo "📱 Phone Number: {$optOut->getPhone()}\n";
    echo '📝 Reason: ' . ($optOut->getReason() ?? 'Not specified') . "\n";
    echo "⏰ Created: {$optOut->getCreatedAt()}\n";
    echo "🔄 Last Updated: {$optOut->getUpdatedAt()}\n";

    echo "\n🚫 This phone number is currently opted out from receiving SMS messages\n";
} catch (NotFoundException $e) {
    echo "❌ No opt-out found with ID: {$optOutId}\n";
    echo "💡 Make sure the opt-out ID exists\n";
} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";

    if ($e->getStatusCode()) {
        echo "🔢 Status Code: {$e->getStatusCode()}\n";
    }

    if ($e->getTraceId()) {
        echo "🆔 Trace ID: {$e->getTraceId()}\n";
    }
}
