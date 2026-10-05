<?php

declare(strict_types=1);

/**
 * Get SMS message details.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

// Replace with an actual message ID
$messageId = 'msg_1234567890';

try {
    echo "=== Get SMS Message ===\n\n";

    // Get message details using fluent chaining
    $response = SmsClient::create($bearerToken)
        ->messages()
        ->get($messageId);

    $message = $response->getData();

    echo "✅ Message retrieved successfully!\n";
    echo "📨 Message ID: {$message->getId()}\n";
    echo "📱 Recipient: {$message->getRecipient()}\n";
    echo "💬 Body: {$message->getBody()}\n";
    echo "📊 Status: {$message->getStatus()}\n";
    echo "🧩 Parts: {$message->getParts()}\n";
    echo "⏰ Created: {$message->getCreatedAt()}\n";
    echo '📅 Scheduled: ' . ($message->getScheduledAt() ?? 'Sent immediately') . "\n";
    echo '📤 Sent: ' . ($message->getSentAt() ?? 'Not sent yet') . "\n";
    echo '📥 Delivered: ' . ($message->getDeliveredAt() ?? 'Not delivered yet') . "\n";
    echo '🔗 Callback URL: ' . ($message->getCallbackUrl() ?? 'None') . "\n";
    echo '👤 Sender: ' . ($message->getSender() ?? 'Default') . "\n";
} catch (NotFoundException $e) {
    echo "❌ Message not found: {$e->getMessage()}\n";
    echo "💡 Please check if the message ID '{$messageId}' exists\n";
} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";

    if ($e->getStatusCode()) {
        echo "🔢 Status Code: {$e->getStatusCode()}\n";
    }

    if ($e->getTraceId()) {
        echo "🆔 Trace ID: {$e->getTraceId()}\n";
    }
}
