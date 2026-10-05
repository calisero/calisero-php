<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Services;

use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Dto\CreateMessageResponse;
use Calisero\Sms\Dto\GetMessageResponse;
use Calisero\Sms\Dto\Message;
use Calisero\Sms\Dto\PaginatedMessages;
use Calisero\Sms\Dto\PaginationLinks;
use Calisero\Sms\Dto\PaginationMeta;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\Http\Response;
use Calisero\Sms\Services\MessageService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessageServiceTest extends TestCase
{
    /** @var HttpClient&MockObject */
    private $httpClient;

    /** @var MessageService */
    private $messageService;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->messageService = new MessageService($this->httpClient);
    }

    public function testCreateMessage(): void
    {
        $request = new CreateMessageRequest(
            '+40742123456',
            'Test message',
            null,
            24,
            null,
            'https://example.com/webhook',
            'TestSender'
        );

        $expectedRequestData = [
            'recipient' => '+40742123456',
            'body' => 'Test message',
            'validity' => 24,
            'callback_url' => 'https://example.com/webhook',
            'sender' => 'TestSender',
        ];

        $responseData = [
            'data' => [
                'id' => 'msg_123456789',
                'recipient' => '+40742123456',
                'body' => 'Test message',
                'visible_body' => null,
                'sender' => 'TestSender',
                'status' => 'pending',
                'parts' => 1,
                'validity' => 24,
                'schedule_at' => null,
                'scheduled_at' => null,
                'sent_at' => null,
                'delivered_at' => null,
                'failed_at' => null,
                'callback_url' => 'https://example.com/webhook',
                'created_at' => '2024-01-01T12:00:00Z',
                'updated_at' => '2024-01-01T12:00:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('/messages', $expectedRequestData, true)
            ->willReturn($responseData);

        $response = $this->messageService->create($request);

        $this->assertInstanceOf(CreateMessageResponse::class, $response);
        $message = $response->getData();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('msg_123456789', $message->getId());
        $this->assertSame('+40742123456', $message->getRecipient());
        $this->assertSame('Test message', $message->getBody());
        $this->assertSame('TestSender', $message->getSender());
        $this->assertSame('pending', $message->getStatus());
        $this->assertSame(1, $message->getParts());
    }

    public function testGetMessage(): void
    {
        $messageId = 'msg_123456789';

        $responseData = [
            'data' => [
                'id' => 'msg_123456789',
                'recipient' => '+40742123456',
                'body' => 'Test message',
                'visible_body' => null,
                'sender' => 'TestSender',
                'status' => 'delivered',
                'parts' => 1,
                'validity' => 24,
                'schedule_at' => null,
                'scheduled_at' => null,
                'sent_at' => '2024-01-01T12:01:00Z',
                'delivered_at' => '2024-01-01T12:02:00Z',
                'failed_at' => null,
                'callback_url' => 'https://example.com/webhook',
                'created_at' => '2024-01-01T12:00:00Z',
                'updated_at' => '2024-01-01T12:02:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with("/messages/{$messageId}")
            ->willReturn($responseData);

        $response = $this->messageService->get($messageId);

        $this->assertInstanceOf(GetMessageResponse::class, $response);
        $message = $response->getData();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('msg_123456789', $message->getId());
        $this->assertSame('delivered', $message->getStatus());
        $this->assertSame('2024-01-01T12:01:00Z', $message->getSentAt());
        $this->assertSame('2024-01-01T12:02:00Z', $message->getDeliveredAt());
    }

    public function testListMessagesFirstPage(): void
    {
        $responseData = [
            'data' => [
                [
                    'id' => 'msg_1',
                    'recipient' => '+40742123456',
                    'body' => 'Test message 1',
                    'visible_body' => null,
                    'sender' => 'TestSender',
                    'status' => 'delivered',
                    'parts' => 1,
                    'validity' => 24,
                    'schedule_at' => null,
                    'scheduled_at' => null,
                    'sent_at' => '2024-01-01T12:01:00Z',
                    'delivered_at' => '2024-01-01T12:02:00Z',
                    'failed_at' => null,
                    'callback_url' => null,
                    'created_at' => '2024-01-01T12:00:00Z',
                    'updated_at' => '2024-01-01T12:02:00Z',
                ],
                [
                    'id' => 'msg_2',
                    'recipient' => '+40742123457',
                    'body' => 'Test message 2',
                    'visible_body' => null,
                    'sender' => 'TestSender',
                    'status' => 'pending',
                    'parts' => 1,
                    'validity' => 24,
                    'schedule_at' => null,
                    'scheduled_at' => null,
                    'sent_at' => null,
                    'delivered_at' => null,
                    'failed_at' => null,
                    'callback_url' => null,
                    'created_at' => '2024-01-01T12:05:00Z',
                    'updated_at' => '2024-01-01T12:05:00Z',
                ],
            ],
            'links' => [
                'first' => 'https://rest.calisero.ro/v1/messages?page=1',
                'last' => 'https://rest.calisero.ro/v1/messages?page=5',
                'prev' => null,
                'next' => 'https://rest.calisero.ro/v1/messages?page=2',
            ],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'path' => '/messages',
                'per_page' => 15,
                'to' => 15,
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('/messages', [])
            ->willReturn($responseData);

        $response = $this->messageService->list();

        $this->assertInstanceOf(PaginatedMessages::class, $response);
        $messages = $response->getData();
        $this->assertCount(2, $messages);
        $this->assertSame('msg_1', $messages[0]->getId());
        $this->assertSame('msg_2', $messages[1]->getId());

        $meta = $response->getMeta();
        $this->assertInstanceOf(PaginationMeta::class, $meta);
        $this->assertSame(1, $meta->getCurrentPage());
        $this->assertSame(15, $meta->getPerPage());

        $links = $response->getLinks();
        $this->assertInstanceOf(PaginationLinks::class, $links);
        $this->assertSame('https://rest.calisero.ro/v1/messages?page=1', $links->getFirst());
        $this->assertSame('https://rest.calisero.ro/v1/messages?page=2', $links->getNext());
        $this->assertNull($links->getPrev());
    }

    public function testListMessagesSpecificPage(): void
    {
        $page = 3;
        $responseData = [
            'data' => [],
            'links' => [
                'first' => 'https://rest.calisero.ro/v1/messages?page=1',
                'last' => 'https://rest.calisero.ro/v1/messages?page=5',
                'prev' => 'https://rest.calisero.ro/v1/messages?page=2',
                'next' => 'https://rest.calisero.ro/v1/messages?page=4',
            ],
            'meta' => [
                'current_page' => 3,
                'from' => 31,
                'path' => '/messages',
                'per_page' => 15,
                'to' => 45,
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with('/messages', ['page' => 3])
            ->willReturn($responseData);

        $response = $this->messageService->list($page);

        $this->assertInstanceOf(PaginatedMessages::class, $response);
        $meta = $response->getMeta();
        $this->assertSame(3, $meta->getCurrentPage());
        $this->assertSame(31, $meta->getFrom());
        $this->assertSame(45, $meta->getTo());
    }

    public function testDeleteMessage(): void
    {
        $messageId = 'msg_123456789';

        $this->httpClient
            ->expects($this->once())
            ->method('delete')
            ->with("/messages/{$messageId}");

        $this->messageService->delete($messageId);
    }

    public function testCreateMinimalMessage(): void
    {
        $request = new CreateMessageRequest(
            '+40742123456',
            'Simple test message'
        );

        $expectedRequestData = [
            'recipient' => '+40742123456',
            'body' => 'Simple test message',
        ];

        $responseData = [
            'data' => [
                'id' => 'msg_simple',
                'recipient' => '+40742123456',
                'body' => 'Simple test message',
                'visible_body' => null,
                'sender' => null,
                'status' => 'pending',
                'parts' => 1,
                'validity' => null,
                'schedule_at' => null,
                'scheduled_at' => null,
                'sent_at' => null,
                'delivered_at' => null,
                'failed_at' => null,
                'callback_url' => null,
                'created_at' => '2024-01-01T12:00:00Z',
                'updated_at' => '2024-01-01T12:00:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('/messages', $expectedRequestData, true)
            ->willReturn($responseData);

        $response = $this->messageService->create($request);

        $message = $response->getData();
        $this->assertSame('msg_simple', $message->getId());
        $this->assertSame('Simple test message', $message->getBody());
        $this->assertNull($message->getSender());

        // Without an answer to read, the response meta is empty.
        $this->assertNull($response->getResponseMeta()->getTraceId());
        $this->assertNull($response->getResponseMeta()->getDailyRemaining());
    }

    public function testCreateMessageWithShortenedUrls(): void
    {
        $request = new CreateMessageRequest(
            '+40742123456',
            'Track your order: https://example.com/orders/12345',
            null,
            null,
            null,
            null,
            null,
            true
        );

        $expectedRequestData = [
            'recipient' => '+40742123456',
            'body' => 'Track your order: https://example.com/orders/12345',
            'shorten_urls' => true,
        ];

        $responseData = [
            'data' => [
                'id' => 'msg_shortened',
                'recipient' => '+40742123456',
                'body' => 'Track your order: https://calisero.ro/s/ghJKPV',
                'parts' => 1,
                'created_at' => '2025-12-02T15:43:02.000000Z',
                'scheduled_at' => null,
                'sent_at' => null,
                'delivered_at' => null,
                'callback_url' => null,
                'status' => 'scheduled',
                'sender' => null,
                'shortened_urls' => [
                    [
                        'id' => '019adfbb-40a1-71ee-bcb5-8d551b8cfdae',
                        'original_link' => 'https://example.com/orders/12345',
                        'shortened_link' => 'https://calisero.ro/s/ghJKPV',
                        'click_count' => 0,
                        'last_click' => null,
                        'created_at' => '2025-12-02T15:43:02.000000Z',
                    ],
                ],
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('/messages', $expectedRequestData, true)
            ->willReturn($responseData);

        $links = $this->messageService->create($request)->getData()->getShortenedUrls();

        $this->assertCount(1, $links);
        $this->assertSame('https://example.com/orders/12345', $links[0]->getOriginalLink());
        $this->assertSame('https://calisero.ro/s/ghJKPV', $links[0]->getShortenedLink());
    }

    public function testCreateMessageReadsTheResponseHeaders(): void
    {
        $request = new CreateMessageRequest('+40742123456', 'Simple test message');

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn([
                'data' => [
                    'id' => 'msg_simple',
                    'recipient' => '+40742123456',
                    'body' => 'Simple test message',
                    'parts' => 1,
                    'created_at' => '2024-01-01T12:00:00Z',
                    'status' => 'scheduled',
                ],
            ]);

        $this->httpClient
            ->expects($this->once())
            ->method('getLastResponse')
            ->willReturn(new Response(201, [
                'X-Trace-Id' => ['9b80eef1-49d4-4502-85a8-febb68cc11a7'],
                'X-RateLimit-Limit' => ['240'],
                'X-RateLimit-Remaining' => ['239'],
                'X-Daily-Limit' => ['1000'],
                'X-Daily-Remaining' => ['873'],
            ], ''));

        $meta = $this->messageService->create($request)->getResponseMeta();

        $this->assertSame('9b80eef1-49d4-4502-85a8-febb68cc11a7', $meta->getTraceId());
        $this->assertSame(240, $meta->getRateLimitLimit());
        $this->assertSame(239, $meta->getRateLimitRemaining());
        $this->assertSame(1000, $meta->getDailyLimit());
        $this->assertSame(873, $meta->getDailyRemaining());
    }

    public function testCreateScheduledMessage(): void
    {
        $request = new CreateMessageRequest(
            '+40742123456',
            'Scheduled message',
            null,
            null,
            '2024-12-25 10:00:00'
        );

        $expectedRequestData = [
            'recipient' => '+40742123456',
            'body' => 'Scheduled message',
            'schedule_at' => '2024-12-25 10:00:00',
        ];

        $responseData = [
            'data' => [
                'id' => 'msg_scheduled',
                'recipient' => '+40742123456',
                'body' => 'Scheduled message',
                'visible_body' => null,
                'sender' => null,
                'status' => 'scheduled',
                'parts' => 1,
                'validity' => null,
                'schedule_at' => '2024-12-25 10:00:00',
                'scheduled_at' => '2024-12-25T10:00:00Z',
                'sent_at' => null,
                'delivered_at' => null,
                'failed_at' => null,
                'callback_url' => null,
                'created_at' => '2024-01-01T12:00:00Z',
                'updated_at' => '2024-01-01T12:00:00Z',
            ],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with('/messages', $expectedRequestData, true)
            ->willReturn($responseData);

        $response = $this->messageService->create($request);

        $message = $response->getData();
        $this->assertSame('msg_scheduled', $message->getId());
        $this->assertSame('scheduled', $message->getStatus());
        $this->assertSame('2024-12-25T10:00:00Z', $message->getScheduledAt());
    }
}
