<?php

declare(strict_types=1);

/**
 * List SMS messages with pagination.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

try {
    echo "=== List SMS Messages ===\n\n";

    // List messages (first page)
    $response = SmsClient::create($bearerToken)->messages()->list();
    $messages = $response->getData();
    $meta = $response->getMeta();
    $links = $response->getLinks();

    echo "✅ Messages listed successfully!\n";
    echo '📊 Messages on this page: ' . count($messages) . "\n";
    echo "📄 Current page: {$meta->getCurrentPage()}\n";
    echo "📄 Per page: {$meta->getPerPage()}\n";
    echo '📄 From record: ' . ($meta->getFrom() ?? 'N/A') . "\n";
    echo '📄 To record: ' . ($meta->getTo() ?? 'N/A') . "\n\n";

    if (count($messages) > 0) {
        echo "📝 Recent messages:\n";
        foreach (array_slice($messages, 0, 5) as $index => $message) {
            echo sprintf(
                "  %d. 📨 %s -> %s: %s [%s]\n",
                $index + 1,
                $message->getId(),
                $message->getRecipient(),
                substr($message->getBody(), 0, 30) . (strlen($message->getBody()) > 30 ? '...' : ''),
                $message->getStatus()
            );
        }

        if (count($messages) > 5) {
            echo '  ... and ' . (count($messages) - 5) . " more messages\n";
        }
    } else {
        echo "📭 No messages found\n";
    }

    // Check if there are more pages
    echo "\n🔗 Pagination:\n";
    echo '  - First page: ' . ($links->getFirst() ? 'Available' : 'N/A') . "\n";
    echo '  - Previous page: ' . ($links->getPrev() ? 'Available' : 'N/A') . "\n";
    echo '  - Next page: ' . ($links->getNext() ? 'Available' : 'N/A') . "\n";
    echo '  - Last page: ' . ($links->getLast() ? 'Available' : 'N/A') . "\n";

    // Example: Get next page if available
    if ($links->getNext()) {
        echo "\n📄 Getting next page...\n";
        $nextPageResponse = SmsClient::create($bearerToken)->messages()->list(2);
        echo '✅ Next page retrieved with ' . count($nextPageResponse->getData()) . " messages\n";
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
