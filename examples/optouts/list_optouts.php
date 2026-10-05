<?php

declare(strict_types=1);

/**
 * List all opt-outs with pagination.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

try {
    echo "=== List OptOuts ===\n\n";

    // List opt-outs (first page)
    $response = SmsClient::create($bearerToken)->optOuts()->list();
    $optOuts = $response->getData();
    $meta = $response->getMeta();
    $links = $response->getLinks();

    echo "✅ OptOuts listed successfully!\n";
    echo '📊 OptOuts on this page: ' . count($optOuts) . "\n";
    echo "📄 Current page: {$meta->getCurrentPage()}\n";
    echo "📄 Per page: {$meta->getPerPage()}\n";
    echo '📄 From record: ' . ($meta->getFrom() ?? 'N/A') . "\n";
    echo '📄 To record: ' . ($meta->getTo() ?? 'N/A') . "\n\n";

    if (count($optOuts) > 0) {
        echo "🚫 Current opt-outs:\n";
        foreach (array_slice($optOuts, 0, 10) as $index => $optOut) {
            $reason = $optOut->getReason();
            $reasonDisplay = $reason
                ? (strlen($reason) > 40 ? substr($reason, 0, 40) . '...' : $reason)
                : 'No reason provided';

            echo sprintf(
                "  %d. 📱 %s [ID: %s]\n     📝 %s\n     ⏰ %s\n\n",
                $index + 1,
                $optOut->getPhone(),
                $optOut->getId(),
                $reasonDisplay,
                $optOut->getCreatedAt()
            );
        }

        if (count($optOuts) > 10) {
            echo '  ... and ' . (count($optOuts) - 10) . " more opt-outs\n\n";
        }
    } else {
        echo "✅ No opt-outs found - all numbers can receive messages\n\n";
    }

    // Pagination info
    echo "🔗 Pagination:\n";
    echo '  - First page: ' . ($links->getFirst() ? 'Available' : 'N/A') . "\n";
    echo '  - Previous page: ' . ($links->getPrev() ? 'Available' : 'N/A') . "\n";
    echo '  - Next page: ' . ($links->getNext() ? 'Available' : 'N/A') . "\n";
    echo '  - Last page: ' . ($links->getLast() ? 'Available' : 'N/A') . "\n";

    // Example: Get next page if available
    if ($links->getNext()) {
        echo "\n📄 Getting next page...\n";
        $nextPageResponse = SmsClient::create($bearerToken)->optOuts()->list(2);
        echo '✅ Next page retrieved with ' . count($nextPageResponse->getData()) . " opt-outs\n";
    }

    // Statistics
    $totalOnPage = count($optOuts);
    if ($totalOnPage > 0) {
        $withReasons = count(array_filter($optOuts, fn ($opt) => $opt->getReason() !== null));
        $withoutReasons = $totalOnPage - $withReasons;

        echo "\n📊 Statistics for this page:\n";
        echo "  - With reasons: {$withReasons}\n";
        echo "  - Without reasons: {$withoutReasons}\n";
    }
} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";

    if ($e->getStatusCode()) {
        echo "🔢 Status Code: {$e->getStatusCode()}\n";
    }

    if ($e->getTraceId()) {
        echo "🆔 Trace ID: {$e->getTraceId()}\n";
    }
}
