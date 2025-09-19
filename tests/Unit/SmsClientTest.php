<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit;

use Calisero\Sms\Services\AccountService;
use Calisero\Sms\Services\MessageService;
use Calisero\Sms\Services\OptOutService;
use Calisero\Sms\SmsClient;
use PHPUnit\Framework\TestCase;

class SmsClientTest extends TestCase
{
    public function testCreateClient(): void
    {
        $bearerToken = 'test-token-123';

        $smsClient = SmsClient::create($bearerToken);

        $this->assertInstanceOf(SmsClient::class, $smsClient);
        $this->assertInstanceOf(MessageService::class, $smsClient->messages());
        $this->assertInstanceOf(OptOutService::class, $smsClient->optOuts());
        $this->assertInstanceOf(AccountService::class, $smsClient->accounts());
    }

    public function testMessagesServiceReturnsSameInstance(): void
    {
        $bearerToken = 'test-token-456';
        $smsClient = SmsClient::create($bearerToken);

        $messageService1 = $smsClient->messages();
        $messageService2 = $smsClient->messages();

        $this->assertSame($messageService1, $messageService2);
    }

    public function testOptOutsServiceReturnsSameInstance(): void
    {
        $bearerToken = 'test-token-789';
        $smsClient = SmsClient::create($bearerToken);

        $optOutService1 = $smsClient->optOuts();
        $optOutService2 = $smsClient->optOuts();

        $this->assertSame($optOutService1, $optOutService2);
    }

    public function testAccountsServiceReturnsSameInstance(): void
    {
        $bearerToken = 'test-token-abc';
        $smsClient = SmsClient::create($bearerToken);

        $accountService1 = $smsClient->accounts();
        $accountService2 = $smsClient->accounts();

        $this->assertSame($accountService1, $accountService2);
    }

    public function testMultipleClientsAreIndependent(): void
    {
        $bearerToken1 = 'client-1-token';
        $bearerToken2 = 'client-2-token';

        $client1 = SmsClient::create($bearerToken1);
        $client2 = SmsClient::create($bearerToken2);

        $this->assertNotSame($client1, $client2);
        $this->assertNotSame($client1->messages(), $client2->messages());
        $this->assertNotSame($client1->optOuts(), $client2->optOuts());
        $this->assertNotSame($client1->accounts(), $client2->accounts());
    }

    public function testServiceInstancesAreDistinct(): void
    {
        $bearerToken = 'test-token-distinct';
        $smsClient = SmsClient::create($bearerToken);

        $messageService = $smsClient->messages();
        $optOutService = $smsClient->optOuts();
        $accountService = $smsClient->accounts();

        $this->assertNotSame($messageService, $optOutService);
        $this->assertNotSame($messageService, $accountService);
        $this->assertNotSame($optOutService, $accountService);
    }
}
