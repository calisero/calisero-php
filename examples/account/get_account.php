<?php

declare(strict_types=1);

/**
 * Get account information and details.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\Exceptions\UnauthorizedException;
use Calisero\Sms\Sms;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

// Replace with your actual account ID
$accountId = 'acc_1234567890';

try {
    // Create the SMS client
    $client = Sms::client($bearerToken);
    $accountService = $client->accounts();

    echo "=== Get Account Information ===\n\n";

    // Get account information
    $response = $accountService->get($accountId);
    $account = $response->getData();

    echo "✅ Account information retrieved successfully!\n\n";

    echo "📋 Basic Information:\n";
    echo "  🆔 Account ID: {$account->getId()}\n";
    echo "  🏷️ Account Code: {$account->getCode()}\n";
    echo "  👤 Name: {$account->getName()}\n";
    echo '  📝 Description: ' . ($account->getDescription() ?? 'Not provided') . "\n";
    echo '  📧 Email: ' . ($account->getEmail() ?? 'Not provided') . "\n";
    echo '  📞 Phone: ' . ($account->getPhone() ?? 'Not provided') . "\n";
    echo '  👨‍💼 Contact Person: ' . ($account->getContactPerson() ?? 'Not provided') . "\n\n";

    echo "💰 Financial Information:\n";
    echo "  💳 Current Credit: {$account->getCredit()}\n";
    echo '  🏦 IBAN: ' . ($account->getIban() ?? 'Not provided') . "\n";
    echo '  📄 Fiscal Code: ' . ($account->getFiscalCode() ?? 'Not provided') . "\n";
    echo '  📋 Registry Number: ' . ($account->getRegistryNumber() ?? 'Not provided') . "\n\n";

    echo "📍 Address Information:\n";
    echo "  🏠 Address: {$account->getAddress()}\n";
    echo "  🏙️ City: {$account->getCity()}\n";
    echo "  🗺️ State: {$account->getState()}\n";
    echo "  🌍 Country: {$account->getCountry()}\n";
    echo '  📮 Postal Code: ' . ($account->getPostalCode() ?? 'Not provided') . "\n\n";

    echo "⚙️ Account Status:\n";
    echo "  📊 Status: {$account->getStatus()}\n";
    echo '  🧪 Sandbox Mode: ' . ($account->isSandbox() ? 'Yes' : 'No') . "\n";
    echo "  ⏰ Created: {$account->getCreatedAt()}\n\n";

    // Credit analysis
    $credit = $account->getCredit();
    echo "💡 Credit Analysis:\n";
    if ($credit > 100) {
        echo "  ✅ Excellent balance - ready for high-volume operations\n";
    } elseif ($credit > 50) {
        echo "  ⚠️ Good balance - monitor for sustained operations\n";
    } elseif ($credit > 10) {
        echo "  🟡 Low balance - consider topping up soon\n";
    } else {
        echo "  🔴 Critical balance - immediate top-up required\n";
    }

    // Environment notice
    if ($account->isSandbox()) {
        echo "\n🧪 SANDBOX NOTICE:\n";
        echo "  This account is in sandbox mode\n";
        echo "  Messages will not be actually delivered\n";
        echo "  Use for testing and development only\n";
    }
} catch (UnauthorizedException $e) {
    echo "❌ Authentication error: {$e->getMessage()}\n";
    echo "💡 Please check your bearer token and ensure it's valid\n";

    if ($e->getRequestId()) {
        echo "🆔 Request ID: {$e->getRequestId()}\n";
    }
} catch (NotFoundException $e) {
    echo "❌ Account not found: {$e->getMessage()}\n";
    echo "💡 Please check if the account ID '{$accountId}' is correct\n";
} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";

    if ($e->getStatusCode()) {
        echo "🔢 Status Code: {$e->getStatusCode()}\n";
    }

    if ($e->getRequestId()) {
        echo "🆔 Request ID: {$e->getRequestId()}\n";
    }

    // Provide specific guidance based on status code
    switch ($e->getStatusCode()) {
        case 401:
            echo "💡 Check your authentication credentials\n";

            break;

        case 403:
            echo "💡 Your account may not have permission to view this account\n";

            break;

        case 404:
            echo "💡 The account ID may be incorrect or the account may not exist\n";

            break;

        case 429:
            echo "💡 You're being rate limited. Wait before retrying\n";

            break;

        case 500:
        case 502:
        case 503:
            echo "💡 Server error. Try again in a few moments\n";

            break;

        default:
            echo "💡 Please check the API documentation for this error code\n";
    }
}
