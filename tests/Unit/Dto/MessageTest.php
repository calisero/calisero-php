<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Dto;

use Calisero\Sms\Dto\Message;
use Calisero\Sms\Dto\ShortenedLink;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testCanCreateFromArray(): void
    {
        $data = [
            'id' => '9e2574e8-3615-4090-9b5a-0fc812079da8',
            'recipient' => '+40742***350',
            'body' => 'Test message!',
            'parts' => 1,
            'created_at' => '2025-02-06T10:18:43.000000Z',
            'scheduled_at' => '2025-02-06T10:18:43.000000Z',
            'sent_at' => null,
            'delivered_at' => null,
            'callback_url' => 'https://yoursite.com/your-callback-url',
            'status' => 'scheduled',
            'sender' => 'CALISERO',
        ];

        $message = Message::fromArray($data);

        $this->assertSame('9e2574e8-3615-4090-9b5a-0fc812079da8', $message->getId());
        $this->assertSame('+40742***350', $message->getRecipient());
        $this->assertSame('Test message!', $message->getBody());
        $this->assertSame(1, $message->getParts());
        $this->assertSame('2025-02-06T10:18:43.000000Z', $message->getCreatedAt());
        $this->assertSame('2025-02-06T10:18:43.000000Z', $message->getScheduledAt());
        $this->assertNull($message->getSentAt());
        $this->assertNull($message->getDeliveredAt());
        $this->assertSame('https://yoursite.com/your-callback-url', $message->getCallbackUrl());
        $this->assertSame('scheduled', $message->getStatus());
        $this->assertSame('CALISERO', $message->getSender());
    }

    public function testCanCreateFromArrayWithNullValues(): void
    {
        $data = [
            'id' => '9e2574e8-3615-4090-9b5a-0fc812079da8',
            'recipient' => '+40742***350',
            'body' => 'Test message!',
            'parts' => 1,
            'created_at' => '2025-02-06T10:18:43.000000Z',
            'status' => 'sent',
        ];

        $message = Message::fromArray($data);

        $this->assertSame('9e2574e8-3615-4090-9b5a-0fc812079da8', $message->getId());
        $this->assertSame('+40742***350', $message->getRecipient());
        $this->assertSame('Test message!', $message->getBody());
        $this->assertSame(1, $message->getParts());
        $this->assertSame('2025-02-06T10:18:43.000000Z', $message->getCreatedAt());
        $this->assertNull($message->getScheduledAt());
        $this->assertNull($message->getSentAt());
        $this->assertNull($message->getDeliveredAt());
        $this->assertNull($message->getCallbackUrl());
        $this->assertSame('sent', $message->getStatus());
        $this->assertNull($message->getSender());
        $this->assertSame([], $message->getShortenedUrls());
    }

    public function testCanCreateFromArrayWithShortenedUrls(): void
    {
        $data = [
            'id' => '9e2574e8-3615-4090-9b5a-0fc812079da8',
            'recipient' => '+40742***350',
            'body' => 'Track your order: https://calisero.ro/s/ghJKPV',
            'parts' => 1,
            'created_at' => '2025-12-02T15:43:02.000000Z',
            'scheduled_at' => null,
            'sent_at' => null,
            'delivered_at' => null,
            'callback_url' => null,
            'status' => 'scheduled',
            'sender' => 'CALISERO',
            'shortened_urls' => [
                [
                    'id' => '019adfbb-40a1-71ee-bcb5-8d551b8cfdae',
                    'original_link' => 'https://example.com/orders/12345',
                    'shortened_link' => 'https://calisero.ro/s/ghJKPV',
                    'click_count' => 0,
                    'last_click' => null,
                    'created_at' => '2025-12-02T15:43:02.000000Z',
                ],
                [
                    'id' => '019adfbb-40a4-70af-ba6c-55b7a07826e6',
                    'original_link' => 'https://example.com/stop',
                    'shortened_link' => 'https://calisero.ro/s/qpmHfD',
                    'click_count' => 2,
                    'last_click' => '2025-12-02T16:00:00.000000Z',
                    'created_at' => '2025-12-02T15:43:02.000000Z',
                ],
            ],
        ];

        $message = Message::fromArray($data);
        $links = $message->getShortenedUrls();

        $this->assertCount(2, $links);
        $this->assertContainsOnlyInstancesOf(ShortenedLink::class, $links);
        $this->assertSame('https://example.com/orders/12345', $links[0]->getOriginalLink());
        $this->assertSame('https://calisero.ro/s/ghJKPV', $links[0]->getShortenedLink());
        $this->assertSame(2, $links[1]->getClickCount());
        $this->assertSame('2025-12-02T16:00:00.000000Z', $links[1]->getLastClick());
    }

    public function testEmptyShortenedUrls(): void
    {
        $message = Message::fromArray([
            'id' => '9e2574e8-3615-4090-9b5a-0fc812079da8',
            'recipient' => '+40742***350',
            'body' => 'Test message!',
            'parts' => 1,
            'created_at' => '2025-02-06T10:18:43.000000Z',
            'status' => 'sent',
            'shortened_urls' => [],
        ]);

        $this->assertSame([], $message->getShortenedUrls());
    }
}
