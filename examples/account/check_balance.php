<?php

declare(strict_types=1);

/**
 * Check account balance and credit status.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\Exceptions\UnauthorizedException;
use Calisero\Sms\SmsClient;

// Replace with your actual API key
$bearerToken = 'your-api-key-here';

// Replace with your actual account ID
$accountId = 'acc_1234567890';

try {
    echo "=== Account Balance Check ===\n\n";

    // Get account information to check balance
    $response = SmsClient::create($bearerToken)->accounts()->get($accountId);
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

    // Daily sending limit: how many messages the account can still send today
    echo "\n📆 Daily Sending Limit:\n";
    $dailyLimit = $account->getDailyLimit();
    $dailyRemaining = $account->getDailyRemaining();

    if ($dailyLimit === null) {
        echo "  ✅ No daily limit applies to this account\n";
    } else {
        echo "  📊 Limit: {$dailyLimit} messages a day (resets at midnight, Romania time)\n";
        echo "  📤 Sent today: {$account->getSentToday()}\n";
        echo "  📥 Left today: {$dailyRemaining}\n";

        if ($dailyRemaining === 0) {
            echo "  🔴 The limit is reached: new messages are refused until midnight\n";
        } elseif ($dailyRemaining !== null && $dailyRemaining < $dailyLimit * 0.2) {
            echo "  🟠 Less than 20% of the limit is left for today\n";
        }

        if ($dailyRemaining !== null && $dailyRemaining < $estimatedMessages) {
            echo "  💡 The daily limit, not the credit, caps what you can send today\n";
        }
    }

    // Service readiness check
    echo "\n🔍 Service Readiness Check:\n";
    $issues = [];

    if ($credit <= 0) {
        $issues[] = 'Insufficient credit balance';
    }

    if ($dailyRemaining === 0) {
        $issues[] = 'Daily sending limit reached until midnight, Romania time';
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
    echo '  📆 Left today: ' . ($dailyRemaining ?? 'no daily limit') . "\n";
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

    if ($e->getTraceId()) {
        echo "🆔 Trace ID: {$e->getTraceId()}\n";
    }
}
