<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Services;

use Calisero\Sms\Dto\Account;
use Calisero\Sms\Dto\GetAccountResponse;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\Services\AccountService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AccountServiceTest extends TestCase
{
    private HttpClient&MockObject $httpClient;
    private AccountService $accountService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->accountService = new AccountService($this->httpClient);
    }

    public function testGetAccount(): void
    {
        $accountId = 'acc_123456789';

        $responseData = [
            'data' => [
                'id' => 'acc_123456789',
                'code' => 'ACME001',
                'name' => 'ACME Corporation',
                'description' => 'Leading technology company',
                'fiscal_code' => 'RO12345678',
                'registry_number' => 'J40/1234/2020',
                'iban' => 'RO49AAAA1B31007593840000',
                'city' => 'Bucharest',
                'state' => 'Bucharest',
                'country' => 'Romania',
                'address' => '123 Main Street',
                'postal_code' => 10001,
                'email' => 'contact@acme.com',
                'phone' => '+40212345678',
                'contact_person' => 'John Doe',
                'credit' => 150.75,
                'status' => 'active',
                'sandbox' => false,
                'created_at' => '2024-01-01T10:00:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with("/accounts/{$accountId}")
            ->willReturn($responseData);

        $response = $this->accountService->get($accountId);

        $this->assertInstanceOf(GetAccountResponse::class, $response);
        $account = $response->getData();
        $this->assertInstanceOf(Account::class, $account);
        
        // Test basic information
        $this->assertSame('acc_123456789', $account->getId());
        $this->assertSame('ACME001', $account->getCode());
        $this->assertSame('ACME Corporation', $account->getName());
        $this->assertSame('Leading technology company', $account->getDescription());
        
        // Test financial information
        $this->assertSame('RO12345678', $account->getFiscalCode());
        $this->assertSame('J40/1234/2020', $account->getRegistryNumber());
        $this->assertSame('RO49AAAA1B31007593840000', $account->getIban());
        $this->assertSame(150.75, $account->getCredit());
        
        // Test location information
        $this->assertSame('Bucharest', $account->getCity());
        $this->assertSame('Bucharest', $account->getState());
        $this->assertSame('Romania', $account->getCountry());
        $this->assertSame('123 Main Street', $account->getAddress());
        $this->assertSame(10001, $account->getPostalCode());
        
        // Test contact information
        $this->assertSame('contact@acme.com', $account->getEmail());
        $this->assertSame('+40212345678', $account->getPhone());
        $this->assertSame('John Doe', $account->getContactPerson());
        
        // Test status and environment
        $this->assertSame('active', $account->getStatus());
        $this->assertFalse($account->isSandbox());
        $this->assertSame('2024-01-01T10:00:00Z', $account->getCreatedAt());
    }

    public function testGetAccountWithMinimalData(): void
    {
        $accountId = 'acc_minimal';

        $responseData = [
            'data' => [
                'id' => 'acc_minimal',
                'code' => 'MIN001',
                'name' => 'Minimal Account',
                'description' => null,
                'fiscal_code' => null,
                'registry_number' => null,
                'iban' => null,
                'city' => 'Unknown City',
                'state' => 'Unknown State',
                'country' => 'Unknown Country',
                'address' => 'Unknown Address',
                'postal_code' => null,
                'email' => null,
                'phone' => null,
                'contact_person' => null,
                'credit' => 0.0,
                'status' => 'pending',
                'sandbox' => true,
                'created_at' => '2024-01-01T08:00:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with("/accounts/{$accountId}")
            ->willReturn($responseData);

        $response = $this->accountService->get($accountId);

        $account = $response->getData();
        
        // Test required fields
        $this->assertSame('acc_minimal', $account->getId());
        $this->assertSame('MIN001', $account->getCode());
        $this->assertSame('Minimal Account', $account->getName());
        $this->assertSame('Unknown City', $account->getCity());
        $this->assertSame('Unknown State', $account->getState());
        $this->assertSame('Unknown Country', $account->getCountry());
        $this->assertSame('Unknown Address', $account->getAddress());
        
        // Test nullable fields
        $this->assertNull($account->getDescription());
        $this->assertNull($account->getFiscalCode());
        $this->assertNull($account->getRegistryNumber());
        $this->assertNull($account->getIban());
        $this->assertNull($account->getPostalCode());
        $this->assertNull($account->getEmail());
        $this->assertNull($account->getPhone());
        $this->assertNull($account->getContactPerson());
        
        // Test defaults
        $this->assertSame(0.0, $account->getCredit());
        $this->assertSame('pending', $account->getStatus());
        $this->assertTrue($account->isSandbox());
    }

    public function testGetAccountWithLowCredit(): void
    {
        $accountId = 'acc_low_credit';

        $responseData = [
            'data' => [
                'id' => 'acc_low_credit',
                'code' => 'LOW001',
                'name' => 'Low Credit Account',
                'description' => 'Account with insufficient funds',
                'fiscal_code' => null,
                'registry_number' => null,
                'iban' => null,
                'city' => 'Budget City',
                'state' => 'Budget State',
                'country' => 'Budget Country',
                'address' => '1 Penny Lane',
                'postal_code' => 1,
                'email' => 'lowcredit@example.com',
                'phone' => '+40100000001',
                'contact_person' => 'Budget Manager',
                'credit' => 0.01,
                'status' => 'active',
                'sandbox' => false,
                'created_at' => '2024-01-01T12:00:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with("/accounts/{$accountId}")
            ->willReturn($responseData);

        $response = $this->accountService->get($accountId);

        $account = $response->getData();
        $this->assertSame('acc_low_credit', $account->getId());
        $this->assertSame(0.01, $account->getCredit());
        $this->assertSame('active', $account->getStatus());
        $this->assertFalse($account->isSandbox());
        $this->assertSame('lowcredit@example.com', $account->getEmail());
        $this->assertSame('Budget Manager', $account->getContactPerson());
    }

    public function testGetInactiveAccount(): void
    {
        $accountId = 'acc_inactive';

        $responseData = [
            'data' => [
                'id' => 'acc_inactive',
                'code' => 'INACTIVE001',
                'name' => 'Inactive Account',
                'description' => 'Suspended account',
                'fiscal_code' => 'INACTIVE123',
                'registry_number' => 'INACTIVE/123/2020',
                'iban' => 'INACTIVE1234567890',
                'city' => 'Suspended City',
                'state' => 'Suspended State',
                'country' => 'Suspended Country',
                'address' => '0 Suspended Street',
                'postal_code' => 0,
                'email' => 'suspended@example.com',
                'phone' => '+40000000000',
                'contact_person' => 'Suspended User',
                'credit' => 50.0,
                'status' => 'suspended',
                'sandbox' => false,
                'created_at' => '2023-01-01T00:00:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with("/accounts/{$accountId}")
            ->willReturn($responseData);

        $response = $this->accountService->get($accountId);

        $account = $response->getData();
        $this->assertSame('acc_inactive', $account->getId());
        $this->assertSame('INACTIVE001', $account->getCode());
        $this->assertSame('Inactive Account', $account->getName());
        $this->assertSame('suspended', $account->getStatus());
        $this->assertSame(50.0, $account->getCredit());
        $this->assertFalse($account->isSandbox());
        $this->assertSame('2023-01-01T00:00:00Z', $account->getCreatedAt());
    }

    public function testGetAccountWithCompleteContactInfo(): void
    {
        $accountId = 'acc_complete';

        $responseData = [
            'data' => [
                'id' => 'acc_complete',
                'code' => 'COMPLETE001',
                'name' => 'Complete Information Account',
                'description' => 'Account with all possible fields filled',
                'fiscal_code' => 'RO98765432',
                'registry_number' => 'J01/9876/2019',
                'iban' => 'RO49BBBB1C31007593840001',
                'city' => 'Cluj-Napoca',
                'state' => 'Cluj',
                'country' => 'Romania',
                'address' => '456 Complete Avenue, Suite 789',
                'postal_code' => 400001,
                'email' => 'complete@completecorp.ro',
                'phone' => '+40264123456',
                'contact_person' => 'Maria Popescu',
                'credit' => 1250.50,
                'status' => 'active',
                'sandbox' => false,
                'created_at' => '2022-06-15T14:30:45Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with("/accounts/{$accountId}")
            ->willReturn($responseData);

        $response = $this->accountService->get($accountId);

        $account = $response->getData();
        
        // Verify all fields are properly set
        $this->assertSame('acc_complete', $account->getId());
        $this->assertSame('COMPLETE001', $account->getCode());
        $this->assertSame('Complete Information Account', $account->getName());
        $this->assertSame('Account with all possible fields filled', $account->getDescription());
        $this->assertSame('RO98765432', $account->getFiscalCode());
        $this->assertSame('J01/9876/2019', $account->getRegistryNumber());
        $this->assertSame('RO49BBBB1C31007593840001', $account->getIban());
        $this->assertSame('Cluj-Napoca', $account->getCity());
        $this->assertSame('Cluj', $account->getState());
        $this->assertSame('Romania', $account->getCountry());
        $this->assertSame('456 Complete Avenue, Suite 789', $account->getAddress());
        $this->assertSame(400001, $account->getPostalCode());
        $this->assertSame('complete@completecorp.ro', $account->getEmail());
        $this->assertSame('+40264123456', $account->getPhone());
        $this->assertSame('Maria Popescu', $account->getContactPerson());
        $this->assertSame(1250.50, $account->getCredit());
        $this->assertSame('active', $account->getStatus());
        $this->assertFalse($account->isSandbox());
        $this->assertSame('2022-06-15T14:30:45Z', $account->getCreatedAt());
    }
}
