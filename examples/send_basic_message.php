<?php

declare(strict_types=1);

/**
 * Basic SMS sending example.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Sms;

// Replace with your actual bearer token
$bearerToken = 'your-bearer-token-here';

try {
    // Create the SMS client
    $client = Sms::client($bearerToken);

    // Create a simple message
    $request = new CreateMessageRequest(
        '+40742***350',  // Replace with actual phone number
        'Hello from Calisero PHP library!'
    );

    // Send the message
    $response = $client->messages()->create($request);
    $message = $response->getData();

    echo '✅ Message sent successfully!' . PHP_EOL;
    echo '📨 Message ID: ' . $message->getId() . PHP_EOL;
    echo '📱 Recipient: ' . $message->getRecipient() . PHP_EOL;
    echo '💬 Body: ' . $message->getBody() . PHP_EOL;
    echo '📊 Status: ' . $message->getStatus() . PHP_EOL;
    echo '🧩 Parts: ' . $message->getParts() . PHP_EOL;
    echo '⏰ Created: ' . $message->getCreatedAt() . PHP_EOL;
} catch (ApiException $e) {
    echo '❌ Error sending message: ' . $e->getMessage() . PHP_EOL;

    if ($e->getStatusCode()) {
        echo '🔢 Status Code: ' . $e->getStatusCode() . PHP_EOL;
    }

    if ($e->getRequestId()) {
        echo '🆔 Request ID: ' . $e->getRequestId() . PHP_EOL;
    }
}
