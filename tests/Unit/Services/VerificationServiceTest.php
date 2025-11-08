<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Services;

use Calisero\Sms\Dto\CreateVerificationRequest;
use Calisero\Sms\Dto\CreateVerificationResponse;
use Calisero\Sms\Dto\GetVerificationResponse;
use Calisero\Sms\Dto\PaginatedVerifications;
use Calisero\Sms\Dto\Verification;
use Calisero\Sms\Dto\VerificationCheckRequest;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\Services\VerificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VerificationServiceTest extends TestCase
{
    /** @var HttpClient&MockObject */
    private $httpClient;

    /** @var VerificationService */
    private $service;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->service = new VerificationService($this->httpClient);
    }

    public function testListVerifications(): void
    {
        $responseData = [
            'data' => [
                [
                    'id' => '019a62f1-66b7-7387-a64f-2742c12a2860',
                    'phone' => '+40742**350',
                    'brand' => 'Calisero',
                    'status' => 'verified',
                    'template' => null,
                    'created_at' => '2025-11-08T10:09:38.000000Z',
                    'expires_at' => '2025-11-08T10:12:38.000000Z',
                    'verified_at' => '2025-11-08T10:10:18.000000Z',
                    'attempts' => 1,
                    'expired' => false,
                ],
            ],
            'links' => [
                'first' => 'https://rest.calisero.ro/api/v1/verifications?page=1',
                'last' => null,
                'prev' => null,
                'next' => 'https://rest.calisero.ro/api/v1/verifications?page=2',
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'path' => 'https://rest.calisero.ro/api/v1/verifications',
                'per_page' => 50,
                'to' => 50,
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('/verifications', [])
            ->willReturn($responseData);

        $response = $this->service->list();

        $this->assertInstanceOf(PaginatedVerifications::class, $response);
        $items = $response->getData();
        $this->assertCount(1, $items);
        $this->assertInstanceOf(Verification::class, $items[0]);
        $this->assertSame('verified', $items[0]->getStatus());
    }

    public function testCreateVerification(): void
    {
        $request = new CreateVerificationRequest('+40742**350', 'Calisero');

        $expectedRequestData = [
            'phone' => '+40742**350',
            'brand' => 'Calisero',
        ];

        $responseData = [
            'data' => [
                'id' => '019a62f1-66b7-7387-a64f-2742c12a2860',
                'phone' => '+40742**350',
                'brand' => 'Calisero',
                'status' => 'unverified',
                'template' => null,
                'created_at' => '2025-11-08T10:09:38.000000Z',
                'expires_at' => '2025-11-08T10:12:38.000000Z',
                'verified_at' => null,
                'attempts' => 0,
                'expired' => false,
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('/verifications', $expectedRequestData, true)
            ->willReturn($responseData);

        $resp = $this->service->create($request);
        $this->assertInstanceOf(CreateVerificationResponse::class, $resp);
        $verification = $resp->getData();
        $this->assertSame('unverified', $verification->getStatus());
    }

    public function testGetVerification(): void
    {
        $id = '019a62f1-66b7-7387-a64f-2742c12a2860';
        $responseData = [
            'data' => [
                'id' => $id,
                'phone' => '+40742**350',
                'brand' => 'Calisero',
                'status' => 'verified',
                'template' => null,
                'created_at' => '2025-11-08T10:09:38.000000Z',
                'expires_at' => '2025-11-08T10:12:38.000000Z',
                'verified_at' => '2025-11-08T10:10:18.000000Z',
                'attempts' => 1,
                'expired' => false,
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('/verifications/' . $id)
            ->willReturn($responseData);

        $resp = $this->service->get($id);
        $this->assertInstanceOf(GetVerificationResponse::class, $resp);
        $this->assertSame('verified', $resp->getData()->getStatus());
    }

    public function testValidateVerification(): void
    {
        $request = new VerificationCheckRequest('+40742**350', 'SBMH0f');

        $responseData = [
            'data' => [
                'id' => '019a62f1-66b7-7387-a64f-2742c12a2860',
                'phone' => '+40742**350',
                'brand' => 'Calisero',
                'status' => 'verified',
                'template' => null,
                'created_at' => '2025-11-08T10:09:38.000000Z',
                'expires_at' => '2025-11-08T10:12:38.000000Z',
                'verified_at' => '2025-11-08T10:10:18.000000Z',
                'attempts' => 1,
                'expired' => false,
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('put')
            ->with('/verifications/validate', ['phone' => '+40742**350', 'code' => 'SBMH0f'])
            ->willReturn($responseData);

        $resp = $this->service->validate($request);
        $this->assertInstanceOf(GetVerificationResponse::class, $resp);
        $this->assertSame('verified', $resp->getData()->getStatus());
    }
}
