<?php

declare(strict_types=1);

/**
 * Send bulk SMS messages to multiple recipients.
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

    // Create client once for bulk operations
    $client = SmsClient::create($bearerToken);

    $successCount = 0;
    $failureCount = 0;
    $results = [];

    echo '📱 Sending messages to ' . count($recipients) . " recipients...\n\n";

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

            // Send using fluent chaining
            $response = $client
                ->messages()
                ->create($request);

            $message = $response->getData();

            echo "✅ Success\n";
            echo "  📨 Message ID: {$message->getId()}\n";
            echo "  📊 Status: {$message->getStatus()}\n";

            // What is left of the account's daily sending limit (null when it has none)
            $dailyRemaining = $response->getResponseMeta()->getDailyRemaining();
            if ($dailyRemaining !== null) {
                echo "  📉 Daily limit: {$dailyRemaining} messages left today\n";
            }
            echo "\n";

            $results[] = [
                'recipient' => $recipient,
                'status' => 'success',
                'message_id' => $message->getId(),
                'parts' => $message->getParts(),
            ];

            ++$successCount;

            // Every further message would be refused until midnight, Romania time
            if ($dailyRemaining === 0) {
                echo "⛔ Daily sending limit used up: the remaining recipients are skipped\n\n";

                break;
            }

            // Add a small delay to avoid rate limiting
            if ($index < count($recipients) - 1) {
                usleep(200000); // 200ms delay
            }
        } catch (DailyLimitExceededException $e) {
            // Nothing was sent or billed, and every further message would be refused too
            echo "❌ Daily sending limit reached\n";
            echo "  💬 Error: {$e->getMessage()}\n\n";

            $results[] = [
                'recipient' => $recipient,
                'status' => 'daily_limit_exceeded',
                'error' => $e->getMessage(),
            ];

            ++$failureCount;

            break;
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
    echo '⏭️ Skipped: ' . (count($recipients) - $successCount - $failureCount) . "\n";
    echo '📊 Total: ' . count($recipients) . "\n";
    echo '📈 Success Rate: ' . round(($successCount / count($recipients)) * 100, 2) . "%\n\n";

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

    if ($e->getTraceId()) {
        echo "🆔 Trace ID: {$e->getTraceId()}\n";
    }

    echo "\n💡 Bulk operation stopped due to critical error\n";
}
