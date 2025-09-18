<?php

declare(strict_types=1);

/**
 * Delete an SMS message (only works for scheduled messages).
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\ForbiddenException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\Sms;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

try {
    // Create the SMS client
    $client = Sms::client($bearerToken);
    $messageService = $client->messages();

    echo "=== Delete SMS Message ===\n\n";

    // First, create a scheduled message that we can delete
    echo "1️⃣ Creating a scheduled message for deletion demo...\n";
    $request = new CreateMessageRequest(
        '+40742***350',
        'This message will be deleted before sending.',
        null,
        1, // 1 hour validity
        date('Y-m-d H:i:s', strtotime('+1 day')) // schedule for tomorrow
    );

    $createResponse = $messageService->create($request);
    $message = $createResponse->getData();
    $messageId = $message->getId();

    echo "✅ Scheduled message created!\n";
    echo "📨 Message ID: {$messageId}\n";
    echo "📅 Scheduled for: {$message->getScheduledAt()}\n";
    echo "📊 Status: {$message->getStatus()}\n\n";

    // Now delete the message
    echo "2️⃣ Deleting the scheduled message...\n";
    $messageService->delete($messageId);

    echo "✅ Message deleted successfully!\n";
    echo "💡 The message '{$messageId}' has been cancelled and will not be sent\n";

    // Verify deletion by trying to get the message
    echo "\n3️⃣ Verifying deletion...\n";

    try {
        $messageService->get($messageId);
        echo "⚠️ Message still exists (some APIs may keep deleted messages with different status)\n";
    } catch (NotFoundException $e) {
        echo "✅ Confirmed: Message no longer exists\n";
    }
} catch (ForbiddenException $e) {
    echo "❌ Cannot delete message: {$e->getMessage()}\n";
    echo "💡 Only scheduled messages that haven't been sent can be deleted\n";
    echo "💡 Messages that are already sent, delivered, or in progress cannot be deleted\n";
} catch (NotFoundException $e) {
    echo "❌ Message not found: {$e->getMessage()}\n";
    echo "💡 The message may have already been deleted or doesn't exist\n";
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
        case 403:
            echo "💡 This message cannot be deleted (likely already sent or in progress)\n";

            break;

        case 404:
            echo "💡 Message not found (may have been already deleted)\n";

            break;

        default:
            echo "💡 Check the API documentation for this error\n";
    }
}
