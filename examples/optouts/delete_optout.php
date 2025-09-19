<?php

declare(strict_types=1);

/**
 * Delete an opt-out (allow phone number to receive messages again).
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

// Replace with the phone number to remove from opt-out list
$phoneNumber = '+40742***350';

try {
    echo "=== Delete OptOut ===\n\n";

    // First, let's check if the opt-out exists
    echo "1️⃣ Checking current opt-out status...\n";

    try {
        $currentResponse = SmsClient::create($bearerToken)->optOuts()->get($phoneNumber);
        $currentOptOut = $currentResponse->getData();

        echo "✅ Opt-out found:\n";
        echo "   📱 Phone: {$currentOptOut->getPhone()}\n";
        echo '   📝 Reason: ' . ($currentOptOut->getReason() ?? 'Not specified') . "\n";
        echo "   ⏰ Created: {$currentOptOut->getCreatedAt()}\n\n";
    } catch (NotFoundException $e) {
        echo "✅ No opt-out found for {$phoneNumber}\n";
        echo "💡 This phone number is already able to receive messages\n";

        return;
    }

    // Delete the opt-out
    echo "2️⃣ Removing opt-out (re-enabling SMS delivery)...\n";
    SmsClient::create($bearerToken)->optOuts()->delete($phoneNumber);

    echo "✅ Opt-out deleted successfully!\n";
    echo "📱 Phone number: {$phoneNumber}\n";
    echo "💡 This phone number can now receive SMS messages again\n";

    // Verify deletion
    echo "\n3️⃣ Verifying deletion...\n";

    try {
        SmsClient::create($bearerToken)->optOuts()->get($phoneNumber);
        echo "⚠️ Opt-out still exists (this may be expected behavior for some APIs)\n";
    } catch (NotFoundException $e) {
        echo "✅ Confirmed: Opt-out has been completely removed\n";
    }

    echo "\n📋 What this means:\n";
    echo "  - ✅ SMS messages can now be sent to {$phoneNumber}\n";
    echo "  - ✅ The number is removed from the opt-out list\n";
    echo "  - ✅ Future marketing messages will be delivered\n";
    echo "  - 💡 The customer can opt-out again if needed\n";
} catch (NotFoundException $e) {
    echo "❌ OptOut not found: {$e->getMessage()}\n";
    echo "💡 The phone number {$phoneNumber} doesn't have an opt-out record\n";
    echo "💡 This means it can already receive messages\n";
} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";

    if ($e->getStatusCode()) {
        echo "🔢 Status Code: {$e->getStatusCode()}\n";
    }

    if ($e->getRequestId()) {
        echo "🆔 Request ID: {$e->getRequestId()}\n";
    }

    // Provide specific guidance
    switch ($e->getStatusCode()) {
        case 404:
            echo "💡 The opt-out record doesn't exist\n";

            break;

        case 403:
            echo "💡 You may not have permission to delete this opt-out\n";

            break;

        default:
            echo "💡 Check the API documentation for this error\n";
    }
}
