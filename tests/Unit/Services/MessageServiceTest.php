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
use Calisero\Sms\Services\MessageService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessageServiceTest extends TestCase
{
    private HttpClient&MockObject $httpClient;
    private MessageService $messageService;

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
