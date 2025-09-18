<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit;

use Calisero\Sms\Contracts\AuthProviderInterface;
use Calisero\Sms\Contracts\HttpClientInterface;
use Calisero\Sms\Contracts\IdempotencyKeyProviderInterface;
use Calisero\Sms\Services\AccountService;
use Calisero\Sms\Services\MessageService;
use Calisero\Sms\Services\OptOutService;
use Calisero\Sms\SmsClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class SmsClientTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    /** @var MockObject&RequestFactoryInterface */
    private $requestFactory;

    /** @var MockObject&StreamFactoryInterface */
    private $streamFactory;

    /** @var AuthProviderInterface&MockObject */
    private $authProvider;

    /** @var IdempotencyKeyProviderInterface&MockObject */
    private $idempotencyKeyProvider;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->authProvider = $this->createMock(AuthProviderInterface::class);
        $this->idempotencyKeyProvider = $this->createMock(IdempotencyKeyProviderInterface::class);
    }

    public function testConstructor(): void
    {
        $smsClient = new SmsClient(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->authProvider,
            'https://rest.calisero.ro/v1',
            $this->idempotencyKeyProvider
        );

        $this->assertInstanceOf(MessageService::class, $smsClient->messages());
        $this->assertInstanceOf(OptOutService::class, $smsClient->optOuts());
        $this->assertInstanceOf(AccountService::class, $smsClient->accounts());
    }

    public function testConstructorWithDefaultParameters(): void
    {
        $smsClient = new SmsClient(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->authProvider
        );

        $this->assertInstanceOf(MessageService::class, $smsClient->messages());
        $this->assertInstanceOf(OptOutService::class, $smsClient->optOuts());
        $this->assertInstanceOf(AccountService::class, $smsClient->accounts());
    }

    public function testCreateWithBearerToken(): void
    {
        $bearerToken = 'test-bearer-token-123';

        $smsClient = SmsClient::create(
            $bearerToken,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory
        );

        $this->assertInstanceOf(SmsClient::class, $smsClient);
        $this->assertInstanceOf(MessageService::class, $smsClient->messages());
        $this->assertInstanceOf(OptOutService::class, $smsClient->optOuts());
        $this->assertInstanceOf(AccountService::class, $smsClient->accounts());
    }

    public function testCreateWithCustomBaseUri(): void
    {
        $bearerToken = 'test-bearer-token-123';
        $customBaseUri = 'https://custom.api.com/v2';

        $smsClient = SmsClient::create(
            $bearerToken,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $customBaseUri
        );

        $this->assertInstanceOf(SmsClient::class, $smsClient);
        $this->assertInstanceOf(MessageService::class, $smsClient->messages());
        $this->assertInstanceOf(OptOutService::class, $smsClient->optOuts());
        $this->assertInstanceOf(AccountService::class, $smsClient->accounts());
    }

    public function testCreateWithIdempotencyKeyProvider(): void
    {
        $bearerToken = 'test-bearer-token-123';

        $smsClient = SmsClient::create(
            $bearerToken,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            'https://rest.calisero.ro/v1',
            $this->idempotencyKeyProvider
        );

        $this->assertInstanceOf(SmsClient::class, $smsClient);
        $this->assertInstanceOf(MessageService::class, $smsClient->messages());
        $this->assertInstanceOf(OptOutService::class, $smsClient->optOuts());
        $this->assertInstanceOf(AccountService::class, $smsClient->accounts());
    }

    public function testMessagesServiceReturnsSameInstance(): void
    {
        $smsClient = new SmsClient(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->authProvider
        );

        $messageService1 = $smsClient->messages();
        $messageService2 = $smsClient->messages();

        $this->assertSame($messageService1, $messageService2);
    }

    public function testOptOutsServiceReturnsSameInstance(): void
    {
        $smsClient = new SmsClient(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->authProvider
        );

        $optOutService1 = $smsClient->optOuts();
        $optOutService2 = $smsClient->optOuts();

        $this->assertSame($optOutService1, $optOutService2);
    }

    public function testAccountsServiceReturnsSameInstance(): void
    {
        $smsClient = new SmsClient(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->authProvider
        );

        $accountService1 = $smsClient->accounts();
        $accountService2 = $smsClient->accounts();

        $this->assertSame($accountService1, $accountService2);
    }

    public function testCreateUsesDefaultBaseUri(): void
    {
        $bearerToken = 'test-bearer-token-123';

        $smsClient = SmsClient::create(
            $bearerToken,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory
        );

        // Just verify that the client was created successfully
        // The actual base URI testing would require inspecting internal state
        // which is not exposed in the public API
        $this->assertInstanceOf(SmsClient::class, $smsClient);
    }

    public function testCreateWithAllParameters(): void
    {
        $bearerToken = 'full-test-token-456';
        $baseUri = 'https://rest.calisero.ro/v1';

        $smsClient = SmsClient::create(
            $bearerToken,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $baseUri,
            $this->idempotencyKeyProvider
        );

        $this->assertInstanceOf(SmsClient::class, $smsClient);

        // Verify all services are available
        $this->assertInstanceOf(MessageService::class, $smsClient->messages());
        $this->assertInstanceOf(OptOutService::class, $smsClient->optOuts());
        $this->assertInstanceOf(AccountService::class, $smsClient->accounts());
    }

    public function testBearerTokenAuthProviderIsUsedInCreate(): void
    {
        $bearerToken = 'bearer-auth-test-789';

        $smsClient = SmsClient::create(
            $bearerToken,
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory
        );

        // The bearer token should be used internally via BearerTokenAuthProvider
        // We can't directly test this without accessing internal state,
        // but we can verify the client was created successfully
        $this->assertInstanceOf(SmsClient::class, $smsClient);
    }

    public function testServiceInstancesAreDistinct(): void
    {
        $smsClient = new SmsClient(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->authProvider
        );

        $messageService = $smsClient->messages();
        $optOutService = $smsClient->optOuts();
        $accountService = $smsClient->accounts();

        // Verify they are different instances
        $this->assertNotSame($messageService, $optOutService);
        $this->assertNotSame($messageService, $accountService);
        $this->assertNotSame($optOutService, $accountService);

        // But verify they are the correct types
        $this->assertInstanceOf(MessageService::class, $messageService);
        $this->assertInstanceOf(OptOutService::class, $optOutService);
        $this->assertInstanceOf(AccountService::class, $accountService);
    }
}
