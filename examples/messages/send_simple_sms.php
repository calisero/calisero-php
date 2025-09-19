<?php

declare(strict_types=1);

/**
 * Send a simple SMS message.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

try {
    echo "=== Send Simple SMS ===\n\n";

    // Create a simple SMS message
    $request = new CreateMessageRequest(
        '+40742***350',  // recipient
        'Hello from Calisero! This is a simple test message.' // body
    );

    // Send SMS using fluent chaining
    $response = SmsClient::create($bearerToken)
        ->messages()
        ->create($request);

    $message = $response->getData();

    echo "✅ Message sent successfully!\n";
    echo "📨 Message ID: {$message->getId()}\n";
    echo "📱 Recipient: {$message->getRecipient()}\n";
    echo "💬 Body: {$message->getBody()}\n";
    echo "📊 Status: {$message->getStatus()}\n";
    echo "🧩 Parts: {$message->getParts()}\n";
    echo "⏰ Created: {$message->getCreatedAt()}\n";
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
