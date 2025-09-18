<?php

declare(strict_types=1);

/**
 * Send bulk SMS messages to multiple recipients.
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

    echo "=== Send Bulk SMS Messages ===\n\n";

    // Define recipients and message content
    $recipients = [
        '+40742***350',
        '+40742***351',
        '+40742***352',
        '+40742***353',
        '+40742***354',
    ];

    $messageBody = 'Important announcement: Our office will be closed tomorrow for maintenance. Thank you for your understanding.';
    $sender = 'Calisero';
    $callbackUrl = 'https://yourapp.com/webhooks/bulk-sms';

    $successCount = 0;
    $failureCount = 0;
    $results = [];

    echo '📱 Sending messages to ' . \count($recipients) . " recipients...\n\n";

    foreach ($recipients as $index => $recipient) {
        try {
            echo "Sending to {$recipient}... ";

            $request = new CreateMessageRequest(
                $recipient,
                $messageBody,
                null, // no visible body override
                24,   // 24 hours validity
                null, // send immediately
                $callbackUrl,
                $sender
            );

            $response = $messageService->create($request);
            $message = $response->getData();

            echo "✅ Success\n";
            echo "  📨 Message ID: {$message->getId()}\n";
            echo "  📊 Status: {$message->getStatus()}\n\n";

            $results[] = [
                'recipient' => $recipient,
                'status' => 'success',
                'message_id' => $message->getId(),
                'parts' => $message->getParts(),
            ];

            ++$successCount;

            // Add a small delay to avoid rate limiting
            if ($index < \count($recipients) - 1) {
                usleep(200000); // 200ms delay
            }
        } catch (ValidationException $e) {
            echo "❌ Validation error\n";
            echo "  💬 Error: {$e->getMessage()}\n\n";

            $results[] = [
                'recipient' => $recipient,
                'status' => 'validation_error',
                'error' => $e->getMessage(),
            ];

            ++$failureCount;
        } catch (ApiException $e) {
            echo "❌ API error\n";
            echo "  💬 Error: {$e->getMessage()}\n";
            echo "  🔢 Status: {$e->getStatusCode()}\n\n";

            $results[] = [
                'recipient' => $recipient,
                'status' => 'api_error',
                'error' => $e->getMessage(),
                'status_code' => $e->getStatusCode(),
            ];

            ++$failureCount;
        }
    }

    // Summary
    echo "=== Bulk SMS Summary ===\n";
    echo "✅ Successful: {$successCount}\n";
    echo "❌ Failed: {$failureCount}\n";
    echo '📊 Total: ' . \count($recipients) . "\n";
    echo '📈 Success Rate: ' . round(($successCount / \count($recipients)) * 100, 2) . "%\n\n";

    // Detailed results
    echo "📝 Detailed Results:\n";
    foreach ($results as $result) {
        echo "  📱 {$result['recipient']}: ";

        if ($result['status'] === 'success') {
            echo "✅ Sent (ID: {$result['message_id']}, Parts: {$result['parts']})\n";
        } else {
            echo "❌ {$result['status']} - {$result['error']}\n";
        }
    }

    if ($successCount > 0) {
        echo "\n💡 Tip: Monitor the callback URL '{$callbackUrl}' for delivery updates\n";
    }
} catch (ApiException $e) {
    echo "❌ Critical API error: {$e->getMessage()}\n";

    if ($e->getStatusCode()) {
        echo "🔢 Status Code: {$e->getStatusCode()}\n";
    }

    if ($e->getRequestId()) {
        echo "🆔 Request ID: {$e->getRequestId()}\n";
    }

    echo "\n💡 Bulk operation stopped due to critical error\n";
}
