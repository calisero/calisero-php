<?php

declare(strict_types=1);

/**
 * Send an advanced SMS message with all options.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\Sms;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

try {
    // Create the SMS client
    $client = Sms::client($bearerToken);
    $messageService = $client->messages();

    echo "=== Send Advanced SMS ===\n\n";

    // Create an advanced SMS with all options
    $request = new CreateMessageRequest(
        '+40742***350',                                          // recipient
        'Your verification code is: 123456. Valid for 24h.',   // body
        'Your verification code is: ******. Valid for 24h.',   // visibleBody (for logs)
        24,                                                      // validity (hours)
        date('Y-m-d H:i:s', strtotime('+1 hour')),             // scheduleAt
        'https://yourapp.com/webhooks/sms',                     // callbackUrl
        'Calisero'                                               // sender
    );

    $response = $messageService->create($request);
    $message = $response->getData();

    echo "✅ Advanced message sent successfully!\n";
    echo "📨 Message ID: {$message->getId()}\n";
    echo "📱 Recipient: {$message->getRecipient()}\n";
    echo "💬 Body: {$message->getBody()}\n";
    echo " Status: {$message->getStatus()}\n";
    echo "🧩 Parts: {$message->getParts()}\n";
    echo "⏰ Created: {$message->getCreatedAt()}\n";
    echo '📅 Scheduled: ' . ($message->getScheduledAt() ?? 'Send immediately') . "\n";
    echo '🔗 Callback URL: ' . ($message->getCallbackUrl() ?? 'None') . "\n";
    echo '👤 Sender: ' . ($message->getSender() ?? 'Default') . "\n";
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
