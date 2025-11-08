<?php

declare(strict_types=1);

/**
 * List verifications with pagination and optional status filter.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

try {
    echo "=== List Verifications ===\n\n";

    // Optional filters
    $page = 1;             // change as needed
    $status = null;        // or 'verified' / 'unverified'

    // List verifications using fluent chaining
    $response = SmsClient::create($bearerToken)
        ->verifications()
        ->list($page, $status);

    $items = $response->getData();
    $meta = $response->getMeta();
    $links = $response->getLinks();

    echo "✅ Verifications listed successfully!\n";
    echo '📊 Total on page: ' . count($items) . "\n";
    echo "📄 Current page: {$meta->getCurrentPage()}\n";
    echo "📄 Per page: {$meta->getPerPage()}\n";
    echo '📄 From record: ' . ($meta->getFrom() ?? 'N/A') . "\n";
    echo '📄 To record: ' . ($meta->getTo() ?? 'N/A') . "\n\n";

    if (count($items) > 0) {
        echo "📝 Recent verifications:\n";
        foreach (array_slice($items, 0, 5) as $index => $v) {
            echo sprintf(
                "  %d. ✅ %s | %s | %s | attempts: %d | expired: %s\n",
                $index + 1,
                $v->getId(),
                $v->getPhone(),
                $v->getStatus(),
                $v->getAttempts(),
                $v->isExpired() ? 'yes' : 'no'
            );
        }
        if (count($items) > 5) {
            echo '  ... and ' . (count($items) - 5) . " more verifications\n";
        }
    } else {
        echo "📭 No verifications found\n";
    }

    echo "\n🔗 Pagination:\n";
    echo '  - First page: ' . ($links->getFirst() ? 'Available' : 'N/A') . "\n";
    echo '  - Previous page: ' . ($links->getPrev() ? 'Available' : 'N/A') . "\n";
    echo '  - Next page: ' . ($links->getNext() ? 'Available' : 'N/A') . "\n";
    echo '  - Last page: ' . ($links->getLast() ? 'Available' : 'N/A') . "\n";

    if ($links->getNext()) {
        echo "\n💡 Tip: Use ->list({$meta->getCurrentPage()} + 1) to fetch the next page.\n";
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
