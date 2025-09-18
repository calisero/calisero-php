<?php

declare(strict_types=1);

/**
 * Advanced SMS sending with all options.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\Sms;

// Replace with your actual bearer token
$bearerToken = 'your-bearer-token-here';

try {
    // Create the SMS client
    $client = Sms::client($bearerToken);

    // Create an advanced message with all options
    $request = new CreateMessageRequest(
        '+40742***350',                                              // Phone number in E.164 format
        'Your verification code is: 123456. Valid for 24 hours.',
        'Your verification code is: ******. Valid for 24 hours.',  // Masked version for logs
        24,                                                           // Message validity in hours
        '2024-12-25 10:00:00',                                      // Schedule for future delivery
        'https://yourapp.com/sms-webhook',                         // Webhook for delivery reports
        'Calisero'                                                    // Custom sender name (needs preapproval)
    );

    // Send the message
    $response = $client->messages()->create($request);
    $message = $response->getData();

    echo '✅ Advanced message scheduled successfully!' . PHP_EOL;
    echo '📨 Message ID: ' . $message->getId() . PHP_EOL;
    echo '📱 Recipient: ' . $message->getRecipient() . PHP_EOL;
    echo '💬 Body: ' . $message->getBody() . PHP_EOL;
    echo '📊 Status: ' . $message->getStatus() . PHP_EOL;
    echo '🧩 Parts: ' . $message->getParts() . PHP_EOL;
    echo '⏰ Created: ' . $message->getCreatedAt() . PHP_EOL;
    echo '📅 Scheduled: ' . ($message->getScheduledAt() ?: 'Immediate') . PHP_EOL;
    echo '🔗 Callback URL: ' . ($message->getCallbackUrl() ?: 'None') . PHP_EOL;
    echo '👤 Sender: ' . ($message->getSender() ?: 'Default') . PHP_EOL;

    // Retrieve the message details
    echo PHP_EOL . '🔍 Retrieving message details...' . PHP_EOL;
    $retrievedResponse = $client->messages()->get($message->getId());
    $retrievedMessage = $retrievedResponse->getData();

    echo '📊 Current status: ' . $retrievedMessage->getStatus() . PHP_EOL;
} catch (ValidationException $e) {
    echo '❌ Validation error: ' . $e->getMessage() . PHP_EOL;

    foreach ($e->getValidationErrors() as $field => $errors) {
        echo "🔸 {$field}: " . implode(', ', $errors) . PHP_EOL;
    }
} catch (ApiException $e) {
    echo '❌ API error: ' . $e->getMessage() . PHP_EOL;

    if ($e->getStatusCode()) {
        echo '🔢 Status Code: ' . $e->getStatusCode() . PHP_EOL;
    }

    if ($e->getRequestId()) {
        echo '🆔 Request ID: ' . $e->getRequestId() . PHP_EOL;
    }
}
