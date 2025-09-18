<?php

declare(strict_types=1);

/**
 * Get opt-out details for a phone number.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\Sms;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

// Replace with the phone number to check
$phoneNumber = '+40742***350';

try {
    // Create the SMS client
    $client = Sms::client($bearerToken);
    $optOutService = $client->optOuts();

    echo "=== Get OptOut Details ===\n\n";

    // Get opt-out details
    $response = $optOutService->get($phoneNumber);
    $optOut = $response->getData();

    echo "✅ Opt-out found!\n";
    echo "🆔 OptOut ID: {$optOut->getId()}\n";
    echo "📱 Phone Number: {$optOut->getPhone()}\n";
    echo '📝 Reason: ' . ($optOut->getReason() ?? 'Not specified') . "\n";
    echo "⏰ Created: {$optOut->getCreatedAt()}\n";
    echo "🔄 Last Updated: {$optOut->getUpdatedAt()}\n";

    echo "\n🚫 This phone number is currently opted out from receiving SMS messages\n";
} catch (NotFoundException $e) {
    echo "✅ No opt-out found for {$phoneNumber}\n";
    echo "💡 This phone number can receive SMS messages\n";
} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";

    if ($e->getStatusCode()) {
        echo "🔢 Status Code: {$e->getStatusCode()}\n";
    }

    if ($e->getRequestId()) {
        echo "🆔 Request ID: {$e->getRequestId()}\n";
    }
}
