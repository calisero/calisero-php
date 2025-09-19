<?php

declare(strict_types=1);

/**
 * Update an existing opt-out record.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Dto\UpdateOptOutRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

// Replace with the opt-out ID to update
$optOutId = 'opt_1234567890abcdef';

try {
    echo "=== Update OptOut ===\n\n";

    // First, let's check the current opt-out
    echo "1️⃣ Checking current opt-out status...\n";

    try {
        $currentResponse = SmsClient::create($bearerToken)->optOuts()->get($optOutId);
        $currentOptOut = $currentResponse->getData();

        echo "✅ Current opt-out found:\n";
        echo "   📱 Phone: {$currentOptOut->getPhone()}\n";
        echo '   📝 Current reason: ' . ($currentOptOut->getReason() ?? 'Not specified') . "\n";
        echo "   ⏰ Created: {$currentOptOut->getCreatedAt()}\n";
        echo "   🔄 Last updated: {$currentOptOut->getUpdatedAt()}\n\n";
    } catch (NotFoundException $e) {
        echo "❌ No opt-out found for {$optOutId}\n";
        echo "💡 You need to create an opt-out first before updating it\n";

        return;
    }

    // Update the opt-out reason
    echo "2️⃣ Updating opt-out reason...\n";
    $updateRequest = new UpdateOptOutRequest(
        $currentOptOut->getPhone(),
        'Updated: Customer called support and confirmed they want to remain opted out due to privacy concerns'
    );

    $updateResponse = SmsClient::create($bearerToken)->optOuts()->update($optOutId, $updateRequest);
    $updatedOptOut = $updateResponse->getData();

    echo "✅ Opt-out updated successfully!\n";
    echo "🆔 OptOut ID: {$updatedOptOut->getId()}\n";
    echo "📱 Phone Number: {$updatedOptOut->getPhone()}\n";
    echo "📝 Updated Reason: {$updatedOptOut->getReason()}\n";
    echo "⏰ Created: {$updatedOptOut->getCreatedAt()}\n";
    echo "🔄 Updated: {$updatedOptOut->getUpdatedAt()}\n";

    echo "\n💡 The opt-out record has been updated with new information\n";
} catch (NotFoundException $e) {
    echo "❌ OptOut not found: {$e->getMessage()}\n";
    echo "💡 Make sure the opt-out ID is existing opt-out record\n";
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

    if ($e->getRequestId()) {
        echo "🆔 Request ID: {$e->getRequestId()}\n";
    }
}
