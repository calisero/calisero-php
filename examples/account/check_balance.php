<?php

declare(strict_types=1);

/**
 * Check account balance and credit status.
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

    echo "=== Account Balance Check ===\n\n";

    // Get account information
    $response = $accountService->get($accountId);
    $account = $response->getData();

    $credit = $account->getCredit();
    $accountName = $account->getName();

    echo "💰 Account Balance Information\n";
    echo "📋 Account: {$accountName} (ID: {$account->getId()})\n";
    echo "💳 Current Credit: {$credit}\n";
    echo "📊 Account Status: {$account->getStatus()}\n";
    echo '🧪 Environment: ' . ($account->isSandbox() ? 'Sandbox' : 'Production') . "\n\n";

    // Balance status analysis
    echo "📈 Balance Analysis:\n";

    if ($credit > 100) {
        echo "  🟢 Status: EXCELLENT\n";
        echo "  💡 Your balance is healthy for regular SMS operations\n";
        echo "  📊 Recommended action: Continue normal operations\n";
        $statusIcon = '✅';
    } elseif ($credit > 50) {
        echo "  🟡 Status: GOOD\n";
        echo "  💡 Balance is adequate but monitor for sustained operations\n";
        echo "  📊 Recommended action: Consider topping up for high-volume campaigns\n";
        $statusIcon = '⚠️';
    } elseif ($credit > 10) {
        echo "  🟠 Status: LOW\n";
        echo "  💡 Balance is running low and needs attention\n";
        echo "  📊 Recommended action: Top up your account soon\n";
        $statusIcon = '🟡';
    } else {
        echo "  🔴 Status: CRITICAL\n";
        echo "  💡 Balance is critically low - immediate action required\n";
        echo "  📊 Recommended action: Top up immediately to maintain service\n";
        $statusIcon = '🚨';
    }

    // Estimated message capacity (assuming average cost)
    $averageCostPerSms = 0.05; // This would typically come from your pricing
    $estimatedMessages = (int) ($credit / $averageCostPerSms);

    echo "\n📱 Estimated Message Capacity:\n";
    echo "  💸 Estimated cost per SMS: {$averageCostPerSms}\n";
    echo "  📊 Approximate messages remaining: ~{$estimatedMessages}\n";

    if ($estimatedMessages > 1000) {
        echo "  ✅ Capacity: Excellent for bulk operations\n";
    } elseif ($estimatedMessages > 100) {
        echo "  ⚠️ Capacity: Good for regular operations\n";
    } elseif ($estimatedMessages > 10) {
        echo "  🟡 Capacity: Limited - suitable for small batches only\n";
    } else {
        echo "  🔴 Capacity: Very limited - top-up required\n";
    }

    // Service readiness check
    echo "\n🔍 Service Readiness Check:\n";
    $issues = [];

    if ($credit <= 0) {
        $issues[] = 'Insufficient credit balance';
    }

    if ($account->getStatus() !== 'active') {
        $issues[] = 'Account status is not active';
    }

    if (empty($issues)) {
        echo "  ✅ All systems ready for SMS operations\n";
        echo "  💡 Your account is properly configured and funded\n";
    } else {
        echo "  ❌ Issues detected:\n";
        foreach ($issues as $issue) {
            echo "    - {$issue}\n";
        }
        echo "  💡 Please resolve these issues before sending SMS messages\n";
    }

    // Summary dashboard
    echo "\n📊 Quick Dashboard:\n";
    echo "  {$statusIcon} Balance: {$credit}\n";
    echo "  📱 Est. Messages: ~{$estimatedMessages}\n";
    echo "  🏷️ Account: {$account->getStatus()}\n";
    echo '  🌍 Environment: ' . ($account->isSandbox() ? 'Sandbox' : 'Production') . "\n";
} catch (UnauthorizedException $e) {
    echo "❌ Authentication error: {$e->getMessage()}\n";
    echo "💡 Please verify your bearer token is valid and not expired\n";
} catch (NotFoundException $e) {
    echo "❌ Account not found: {$e->getMessage()}\n";
    echo "💡 Please verify the account ID '{$accountId}' is correct\n";
} catch (ApiException $e) {
    echo "❌ API error: {$e->getMessage()}\n";

    if ($e->getStatusCode()) {
        echo "🔢 Status Code: {$e->getStatusCode()}\n";
    }

    if ($e->getRequestId()) {
        echo "🆔 Request ID: {$e->getRequestId()}\n";
    }
}
