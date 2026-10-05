<?php

declare(strict_types=1);

/**
 * Receive delivery status callbacks.
 *
 * Calisero posts a JSON payload to a message's callback URL each time its status
 * changes (sent, delivered or undelivered). Answer with any 2xx status within
 * 2 seconds: only a failed connection or a slower answer is retried, at most
 * 5 attempts in total; a non-2xx answer is not retried.
 *
 * Serve this file at the URL you pass as callbackUrl, or try it locally:
 *
 *   php -S localhost:8080 examples/webhooks/delivery_webhook.php
 *
 *   curl -X POST http://localhost:8080 -H 'Content-Type: application/json' \
 *        -d '{"messageId":"019961db-14a7-7348-963f-5a7a789a969f","recipient":"+40742***350","status":"delivered","price":0.0378}'
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Dto\DeliveryWebhookMessage;

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo '{"error":"Method not allowed"}';

    exit;
}

try {
    $webhook = DeliveryWebhookMessage::fromJson((string) file_get_contents('php://input'));
} catch (InvalidArgumentException $e) {
    // A malformed payload is answered 400 and not retried
    error_log($e->getMessage());
    http_response_code(400);
    echo '{"error":"Invalid payload"}';

    exit;
}

switch ($webhook->getStatus()) {
    case 'delivered':
        $line = "📬 Message {$webhook->getMessageId()} delivered to {$webhook->getRecipient()} at {$webhook->getDeliveredAt()}";

        break;

    case 'undelivered':
        $line = "❌ Message {$webhook->getMessageId()} could not be delivered to {$webhook->getRecipient()}";

        break;

    default:
        $line = "📤 Message {$webhook->getMessageId()} sent to {$webhook->getRecipient()} at {$webhook->getSentAt()}";
}

$line .= sprintf(' | price: %.4f | balance: %s', $webhook->getPrice(), $webhook->getRemainingBalance() ?? 'N/A');

// The account's daily sending limit as it stood when the callback was sent
if ($webhook->getDailyLimit() !== null) {
    $line .= " | daily limit: {$webhook->getDailyRemaining()} of {$webhook->getDailyLimit()} messages left today";
}

// Keep the work here short (update a record, queue a job), then answer
error_log($line);

http_response_code(200);
echo '{"received":true}';
