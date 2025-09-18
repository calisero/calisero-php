<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit;

use Calisero\Sms\Contracts\HttpClientInterface;
use Calisero\Sms\Sms;
use Calisero\Sms\SmsClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SmsTest extends TestCase
{
    public function testClientWithMinimalParameters(): void
    {
        $bearerToken = 'test-token-123';

        $client = Sms::client($bearerToken);

        $this->assertInstanceOf(SmsClient::class, $client);
    }

    public function testClientWithCustomBaseUri(): void
    {
        $bearerToken = 'test-token-456';
        $baseUri = 'https://custom.api.endpoint.com/v2';

        $client = Sms::client($bearerToken, $baseUri);

        $this->assertInstanceOf(SmsClient::class, $client);
    }

    public function testClientWithOptions(): void
    {
        $bearerToken = 'test-token-789';
        $baseUri = null;
        $options = [
            'timeout' => 60,
            'connect_timeout' => 15,
            'verify' => false,
        ];

        $client = Sms::client($bearerToken, $baseUri, $options);

        $this->assertInstanceOf(SmsClient::class, $client);
    }

    public function testClientWithAllParameters(): void
    {
        $bearerToken = 'full-test-token';
        $baseUri = 'https://staging.api.calisero.ro/v1';
        $options = [
            'timeout' => 45,
            'connect_timeout' => 5,
            'headers' => [
                'User-Agent' => 'Custom-SMS-Client/1.0',
            ],
        ];

        $client = Sms::client($bearerToken, $baseUri, $options);

        $this->assertInstanceOf(SmsClient::class, $client);
    }

    public function testClientUsesDefaultBaseUriWhenNull(): void
    {
        $bearerToken = 'default-uri-test';

        $client = Sms::client($bearerToken, null);

        $this->assertInstanceOf(SmsClient::class, $client);
    }

    public function testClientWithEmptyOptions(): void
    {
        $bearerToken = 'empty-options-test';

        $client = Sms::client($bearerToken, null, []);

        $this->assertInstanceOf(SmsClient::class, $client);
    }

    public function testClientWithCustomHttpClient(): void
    {
        $bearerToken = 'custom-http-client-test';
        /** @var HttpClientInterface&MockObject $httpClient */
        $httpClient = $this->createMock(HttpClientInterface::class);

        $client = Sms::clientWith($bearerToken, $httpClient);

        $this->assertInstanceOf(SmsClient::class, $client);
    }

    public function testClientWithCustomHttpClientAndBaseUri(): void
    {
        $bearerToken = 'custom-client-uri-test';
        /** @var HttpClientInterface&MockObject $httpClient */
        $httpClient = $this->createMock(HttpClientInterface::class);
        $baseUri = 'https://custom.endpoint.com/v3';

        $client = Sms::clientWith($bearerToken, $httpClient, $baseUri);

        $this->assertInstanceOf(SmsClient::class, $client);
    }

    public function testClientWithCustomHttpClientUsesDefaultBaseUri(): void
    {
        $bearerToken = 'custom-client-default-uri';
        /** @var HttpClientInterface&MockObject $httpClient */
        $httpClient = $this->createMock(HttpClientInterface::class);

        $client = Sms::clientWith($bearerToken, $httpClient, null);

        $this->assertInstanceOf(SmsClient::class, $client);
    }

    public function testMultipleClientInstancesAreIndependent(): void
    {
        $bearerToken1 = 'client-1-token';
        $bearerToken2 = 'client-2-token';

        $client1 = Sms::client($bearerToken1);
        $client2 = Sms::client($bearerToken2);

        $this->assertInstanceOf(SmsClient::class, $client1);
        $this->assertInstanceOf(SmsClient::class, $client2);
        $this->assertNotSame($client1, $client2);
    }

    public function testClientServicesAreAccessible(): void
    {
        $bearerToken = 'services-test-token';

        $client = Sms::client($bearerToken);

        // Verify that all services are accessible
        $this->assertNotNull($client->messages());
        $this->assertNotNull($client->optOuts());
        $this->assertNotNull($client->accounts());
    }

    public function testClientWithProductionLikeConfiguration(): void
    {
        $bearerToken = 'prod-like-token-xyz';
        $baseUri = 'https://rest.calisero.ro/api/v1';
        $options = [
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => true,
            'headers' => [
                'User-Agent' => 'MyApp SMS Client/1.0',
                'Accept' => 'application/json',
            ],
        ];

        $client = Sms::client($bearerToken, $baseUri, $options);

        $this->assertInstanceOf(SmsClient::class, $client);
    }

    public function testClientWithDevelopmentConfiguration(): void
    {
        $bearerToken = 'dev-token-123';
        $baseUri = 'https://rest.calisero.ro/v1';
        $options = [
            'timeout' => 60,
            'connect_timeout' => 20,
            'verify' => false,
            'debug' => true,
        ];

        $client = Sms::client($bearerToken, $baseUri, $options);

        $this->assertInstanceOf(SmsClient::class, $client);
    }

    public function testClientWithMixedCustomOptions(): void
    {
        $bearerToken = 'mixed-options-token';
        $options = [
            'timeout' => 25,        // Override default
            'connect_timeout' => 8, // Override default
            'proxy' => 'http://proxy.example.com:8080',
            'allow_redirects' => false,
        ];

        $client = Sms::client($bearerToken, null, $options);

        $this->assertInstanceOf(SmsClient::class, $client);
    }
}
