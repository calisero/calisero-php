<?php

declare(strict_types=1);

/**
 * Example demonstrating opt-out management.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Calisero\Sms\Dto\CreateOptOutRequest;
use Calisero\Sms\Dto\UpdateOptOutRequest;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Sms;

// Replace with your actual bearer token
$bearerToken = 'your-bearer-token-here';

try {
    // Create the SMS client
    $client = Sms::client($bearerToken);

    // Create a new opt-out
    echo '📝 Creating opt-out...' . PHP_EOL;
    $createRequest = new CreateOptOutRequest(
        '+40742***350',
        'User requested opt-out via website form'
    );

    $createResponse = $client->optOuts()->create($createRequest);
    $optOut = $createResponse->getData();

    echo '✅ Opt-out created successfully!' . PHP_EOL;
    echo '🆔 ID: ' . $optOut->getId() . PHP_EOL;
    echo '📱 Phone: ' . $optOut->getPhone() . PHP_EOL;
    echo '📝 Reason: ' . ($optOut->getReason() ?: 'No reason provided') . PHP_EOL;
    echo '⏰ Created: ' . $optOut->getCreatedAt() . PHP_EOL;

    // Update the opt-out
    echo PHP_EOL . '✏️ Updating opt-out...' . PHP_EOL;
    $updateRequest = new UpdateOptOutRequest(
        '+40742***350',
        'Updated: User confirmed opt-out via email'
    );

    $updateResponse = $client->optOuts()->update($optOut->getId(), $updateRequest);
    $updatedOptOut = $updateResponse->getData();

    echo '✅ Opt-out updated successfully!' . PHP_EOL;
    echo '📝 New reason: ' . $updatedOptOut->getReason() . PHP_EOL;
    echo '🔄 Updated: ' . $updatedOptOut->getUpdatedAt() . PHP_EOL;

    // List all opt-outs
    echo PHP_EOL . '📋 Listing opt-outs...' . PHP_EOL;
    $listResponse = $client->optOuts()->list();

    echo '📊 Total opt-outs on this page: ' . \count($listResponse->getData()) . PHP_EOL;
    echo '📄 Current page: ' . $listResponse->getMeta()->getCurrentPage() . PHP_EOL;
    echo '📑 Per page: ' . $listResponse->getMeta()->getPerPage() . PHP_EOL;

    foreach ($listResponse->getData() as $opt) {
        echo '  📱 ' . $opt->getPhone() . ' - ' . ($opt->getReason() ?: 'No reason') . PHP_EOL;
    }

    // Get specific opt-out
    echo PHP_EOL . '🔍 Retrieving specific opt-out...' . PHP_EOL;
    $getResponse = $client->optOuts()->get($optOut->getId());
    $retrievedOptOut = $getResponse->getData();

    echo '✅ Retrieved opt-out:' . PHP_EOL;
    echo '📱 Phone: ' . $retrievedOptOut->getPhone() . PHP_EOL;
    echo '📝 Reason: ' . ($retrievedOptOut->getReason() ?: 'No reason provided') . PHP_EOL;

    // Delete the opt-out (uncomment if you want to delete)
    // echo PHP_EOL . '🗑️ Deleting opt-out...' . PHP_EOL;
    // $client->optOuts()->delete($optOut->getId());
    // echo '✅ Opt-out deleted successfully!' . PHP_EOL;
} catch (ApiException $e) {
    echo '❌ Error managing opt-outs: ' . $e->getMessage() . PHP_EOL;

    if ($e->getStatusCode()) {
        echo '🔢 Status Code: ' . $e->getStatusCode() . PHP_EOL;
    }

    if ($e->getRequestId()) {
        echo '🆔 Request ID: ' . $e->getRequestId() . PHP_EOL;
    }
}
