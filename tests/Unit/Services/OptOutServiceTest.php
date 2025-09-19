<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Services;

use Calisero\Sms\Dto\CreateOptOutRequest;
use Calisero\Sms\Dto\CreateOptOutResponse;
use Calisero\Sms\Dto\GetOptOutResponse;
use Calisero\Sms\Dto\OptOut;
use Calisero\Sms\Dto\PaginatedOptOuts;
use Calisero\Sms\Dto\PaginationLinks;
use Calisero\Sms\Dto\PaginationMeta;
use Calisero\Sms\Dto\UpdateOptOutRequest;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\Services\OptOutService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptOutServiceTest extends TestCase
{
    /** @var HttpClient&MockObject */
    private $httpClient;

    /** @var OptOutService */
    private $optOutService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->optOutService = new OptOutService($this->httpClient);
    }

    public function testCreateOptOut(): void
    {
        $request = new CreateOptOutRequest(
            '+40742123456',
            'Customer requested to stop receiving marketing messages'
        );

        $expectedRequestData = [
            'phone' => '+40742123456',
            'reason' => 'Customer requested to stop receiving marketing messages',
        ];

        $responseData = [
            'data' => [
                'id' => 'opt_123456789',
                'phone' => '+40742123456',
                'reason' => 'Customer requested to stop receiving marketing messages',
                'created_at' => '2024-01-01T12:00:00Z',
                'updated_at' => '2024-01-01T12:00:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('/opt-outs', $expectedRequestData)
            ->willReturn($responseData);

        $response = $this->optOutService->create($request);

        $this->assertInstanceOf(CreateOptOutResponse::class, $response);
        $optOut = $response->getData();
        $this->assertInstanceOf(OptOut::class, $optOut);
        $this->assertSame('opt_123456789', $optOut->getId());
        $this->assertSame('+40742123456', $optOut->getPhone());
        $this->assertSame('Customer requested to stop receiving marketing messages', $optOut->getReason());
        $this->assertSame('2024-01-01T12:00:00Z', $optOut->getCreatedAt());
        $this->assertSame('2024-01-01T12:00:00Z', $optOut->getUpdatedAt());
    }

    public function testCreateOptOutWithoutReason(): void
    {
        $request = new CreateOptOutRequest('+40742123456');

        $expectedRequestData = [
            'phone' => '+40742123456',
        ];

        $responseData = [
            'data' => [
                'id' => 'opt_no_reason',
                'phone' => '+40742123456',
                'reason' => null,
                'created_at' => '2024-01-01T12:00:00Z',
                'updated_at' => '2024-01-01T12:00:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('/opt-outs', $expectedRequestData)
            ->willReturn($responseData);

        $response = $this->optOutService->create($request);

        $optOut = $response->getData();
        $this->assertSame('opt_no_reason', $optOut->getId());
        $this->assertSame('+40742123456', $optOut->getPhone());
        $this->assertNull($optOut->getReason());
    }

    public function testGetOptOut(): void
    {
        $optOutId = 'opt_123456789';

        $responseData = [
            'data' => [
                'id' => 'opt_123456789',
                'phone' => '+40742123456',
                'reason' => 'Customer requested to stop receiving marketing messages',
                'created_at' => '2024-01-01T12:00:00Z',
                'updated_at' => '2024-01-01T12:30:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with("/opt-outs/{$optOutId}")
            ->willReturn($responseData);

        $response = $this->optOutService->get($optOutId);

        $this->assertInstanceOf(GetOptOutResponse::class, $response);
        $optOut = $response->getData();
        $this->assertInstanceOf(OptOut::class, $optOut);
        $this->assertSame('opt_123456789', $optOut->getId());
        $this->assertSame('+40742123456', $optOut->getPhone());
        $this->assertSame('Customer requested to stop receiving marketing messages', $optOut->getReason());
        $this->assertSame('2024-01-01T12:00:00Z', $optOut->getCreatedAt());
        $this->assertSame('2024-01-01T12:30:00Z', $optOut->getUpdatedAt());
    }

    public function testListOptOutsFirstPage(): void
    {
        $responseData = [
            'data' => [
                [
                    'id' => 'opt_1',
                    'phone' => '+40742123456',
                    'reason' => 'Marketing opt-out',
                    'created_at' => '2024-01-01T10:00:00Z',
                    'updated_at' => '2024-01-01T10:00:00Z',
                ],
                [
                    'id' => 'opt_2',
                    'phone' => '+40742123457',
                    'reason' => null,
                    'created_at' => '2024-01-01T11:00:00Z',
                    'updated_at' => '2024-01-01T11:00:00Z',
                ],
            ],
            'links' => [
                'first' => 'https://rest.calisero.ro/v1/opt-outs?page=1',
                'last' => 'https://rest.calisero.ro/v1/opt-outs?page=3',
                'prev' => null,
                'next' => 'https://rest.calisero.ro/v1/opt-outs?page=2',
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'path' => '/opt-outs',
                'per_page' => 15,
                'to' => 15,
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('/opt-outs', [])
            ->willReturn($responseData);

        $response = $this->optOutService->list();

        $this->assertInstanceOf(PaginatedOptOuts::class, $response);
        $optOuts = $response->getData();
        $this->assertCount(2, $optOuts);

        $this->assertSame('opt_1', $optOuts[0]->getId());
        $this->assertSame('+40742123456', $optOuts[0]->getPhone());
        $this->assertSame('Marketing opt-out', $optOuts[0]->getReason());

        $this->assertSame('opt_2', $optOuts[1]->getId());
        $this->assertSame('+40742123457', $optOuts[1]->getPhone());
        $this->assertNull($optOuts[1]->getReason());

        $meta = $response->getMeta();
        $this->assertInstanceOf(PaginationMeta::class, $meta);
        $this->assertSame(1, $meta->getCurrentPage());
        $this->assertSame(15, $meta->getPerPage());

        $links = $response->getLinks();
        $this->assertInstanceOf(PaginationLinks::class, $links);
        $this->assertSame('https://rest.calisero.ro/v1/opt-outs?page=1', $links->getFirst());
        $this->assertSame('https://rest.calisero.ro/v1/opt-outs?page=2', $links->getNext());
        $this->assertNull($links->getPrev());
    }

    public function testListOptOutsSpecificPage(): void
    {
        $page = 2;
        $responseData = [
            'data' => [
                [
                    'id' => 'opt_page2_1',
                    'phone' => '+40742123458',
                    'reason' => 'Privacy concerns',
                    'created_at' => '2024-01-01T12:00:00Z',
                    'updated_at' => '2024-01-01T12:00:00Z',
                ],
            ],
            'links' => [
                'first' => 'https://rest.calisero.ro/v1/opt-outs?page=1',
                'last' => 'https://rest.calisero.ro/v1/opt-outs?page=3',
                'prev' => 'https://rest.calisero.ro/v1/opt-outs?page=1',
                'next' => 'https://rest.calisero.ro/v1/opt-outs?page=3',
            ],
            'meta' => [
                'current_page' => 2,
                'from' => 16,
                'path' => '/opt-outs',
                'per_page' => 15,
                'to' => 30,
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('/opt-outs', ['page' => 2])
            ->willReturn($responseData);

        $response = $this->optOutService->list($page);

        $this->assertInstanceOf(PaginatedOptOuts::class, $response);
        $optOuts = $response->getData();
        $this->assertCount(1, $optOuts);
        $this->assertSame('opt_page2_1', $optOuts[0]->getId());

        $meta = $response->getMeta();
        $this->assertSame(2, $meta->getCurrentPage());
        $this->assertSame(16, $meta->getFrom());
        $this->assertSame(30, $meta->getTo());
    }

    public function testUpdateOptOut(): void
    {
        $optOutId = 'opt_123456789';
        $request = new UpdateOptOutRequest(
            '+40742123456',
            'Updated: Customer confirmed opt-out via phone call'
        );

        $expectedRequestData = [
            'phone' => '+40742123456',
            'reason' => 'Updated: Customer confirmed opt-out via phone call',
        ];

        $responseData = [
            'data' => [
                'id' => 'opt_123456789',
                'phone' => '+40742123456',
                'reason' => 'Updated: Customer confirmed opt-out via phone call',
                'created_at' => '2024-01-01T12:00:00Z',
                'updated_at' => '2024-01-01T14:30:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('put')
            ->with("/opt-outs/{$optOutId}", $expectedRequestData)
            ->willReturn($responseData);

        $response = $this->optOutService->update($optOutId, $request);

        $this->assertInstanceOf(GetOptOutResponse::class, $response);
        $optOut = $response->getData();
        $this->assertSame('opt_123456789', $optOut->getId());
        $this->assertSame('+40742123456', $optOut->getPhone());
        $this->assertSame('Updated: Customer confirmed opt-out via phone call', $optOut->getReason());
        $this->assertSame('2024-01-01T12:00:00Z', $optOut->getCreatedAt());
        $this->assertSame('2024-01-01T14:30:00Z', $optOut->getUpdatedAt());
    }

    public function testUpdateOptOutWithoutReason(): void
    {
        $optOutId = 'opt_123456789';
        $request = new UpdateOptOutRequest('+40742123456');

        $expectedRequestData = [
            'phone' => '+40742123456',
        ];

        $responseData = [
            'data' => [
                'id' => 'opt_123456789',
                'phone' => '+40742123456',
                'reason' => null,
                'created_at' => '2024-01-01T12:00:00Z',
                'updated_at' => '2024-01-01T14:30:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('put')
            ->with("/opt-outs/{$optOutId}", $expectedRequestData)
            ->willReturn($responseData);

        $response = $this->optOutService->update($optOutId, $request);

        $optOut = $response->getData();
        $this->assertSame('opt_123456789', $optOut->getId());
        $this->assertNull($optOut->getReason());
        $this->assertSame('2024-01-01T14:30:00Z', $optOut->getUpdatedAt());
    }

    public function testDeleteOptOut(): void
    {
        $optOutId = 'opt_123456789';

        $this->httpClient
            ->expects($this->once())
            ->method('delete')
            ->with("/opt-outs/{$optOutId}");

        $this->optOutService->delete($optOutId);
    }

    public function testCreateOptOutWithLongReason(): void
    {
        $longReason = 'This is a very long reason for the opt-out request that exceeds normal length to test how the system handles longer explanations from customers who want to provide detailed feedback about their decision to opt out from receiving SMS messages from our service.';

        $request = new CreateOptOutRequest('+40742123456', $longReason);

        $expectedRequestData = [
            'phone' => '+40742123456',
            'reason' => $longReason,
        ];

        $responseData = [
            'data' => [
                'id' => 'opt_long_reason',
                'phone' => '+40742123456',
                'reason' => $longReason,
                'created_at' => '2024-01-01T12:00:00Z',
                'updated_at' => '2024-01-01T12:00:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('/opt-outs', $expectedRequestData)
            ->willReturn($responseData);

        $response = $this->optOutService->create($request);

        $optOut = $response->getData();
        $this->assertSame('opt_long_reason', $optOut->getId());
        $this->assertSame($longReason, $optOut->getReason());
    }

    public function testListOptOutsEmptyResult(): void
    {
        $responseData = [
            'data' => [],
            'links' => [
                'first' => 'https://rest.calisero.ro/v1/opt-outs?page=1',
                'last' => 'https://rest.calisero.ro/v1/opt-outs?page=1',
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                'current_page' => 1,
                'from' => null,
                'path' => '/opt-outs',
                'per_page' => 15,
                'to' => null,
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('/opt-outs', [])
            ->willReturn($responseData);

        $response = $this->optOutService->list();

        $this->assertInstanceOf(PaginatedOptOuts::class, $response);
        $optOuts = $response->getData();
        $this->assertCount(0, $optOuts);

        $meta = $response->getMeta();
        $this->assertSame(1, $meta->getCurrentPage());
        $this->assertNull($meta->getFrom());
        $this->assertNull($meta->getTo());

        $links = $response->getLinks();
        $this->assertNull($links->getNext());
        $this->assertNull($links->getPrev());
    }
}
