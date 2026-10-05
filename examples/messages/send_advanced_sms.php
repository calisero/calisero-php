<?php

declare(strict_types=1);

/**
 * Send an advanced SMS message with all options.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\DailyLimitExceededException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

try {
    echo "=== Send Advanced SMS ===\n\n";

    // Create an advanced SMS with all options
    $request = new CreateMessageRequest(
        '+40742***350',                                                          // recipient
        'Your code is 123456. Manage alerts: https://yourapp.com/account/alerts', // body
        'Your code is ******. Manage alerts: https://yourapp.com/account/alerts', // visibleBody (for logs)
        24,                                                                      // validity (hours)
        date('Y-m-d H:i:s', strtotime('+1 hour')),                               // scheduleAt
        'https://yourapp.com/webhooks/sms',                                      // callbackUrl
        'Calisero',                                                              // sender
        true                                                                     // shortenUrls
    );

    // Send advanced SMS using fluent chaining
    $response = SmsClient::create($bearerToken)
        ->messages()
        ->create($request);

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

    // The links of the body that were replaced by short ones
    $shortenedUrls = $message->getShortenedUrls();
    echo '✂️ Shortened URLs: ' . (count($shortenedUrls) > 0 ? count($shortenedUrls) : 'None') . "\n";
    foreach ($shortenedUrls as $link) {
        echo "  - {$link->getShortenedLink()} → {$link->getOriginalLink()}\n";
    }

    // What the answer's headers report: trace ID and the account's daily sending limit
    $meta = $response->getResponseMeta();
    echo '🆔 Trace ID: ' . ($meta->getTraceId() ?? 'N/A') . "\n";
    if ($meta->getDailyLimit() !== null) {
        echo "📊 Daily limit: {$meta->getDailyRemaining()} of {$meta->getDailyLimit()} messages left today\n";
    }
} catch (ValidationException $e) {
    echo "❌ Validation error: {$e->getMessage()}\n";

    if ($e->getValidationErrors()) {
        echo "📝 Validation details:\n";
        foreach ($e->getValidationErrors() as $field => $errors) {
            echo "  - {$field}: " . implode(', ', $errors) . "\n";
        }
    }
} catch (DailyLimitExceededException $e) {
    echo "❌ Daily sending limit reached: {$e->getMessage()}\n";
    echo '⏰ Sending resumes at: ' . ($e->getResetsAt() ?? 'midnight, Romania time') . "\n";
} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";

    if ($e->getStatusCode()) {
        echo "🔢 Status Code: {$e->getStatusCode()}\n";
    }

    if ($e->getTraceId()) {
        echo "🆔 Trace ID: {$e->getTraceId()}\n";
    }
}
