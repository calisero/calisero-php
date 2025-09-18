<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Dto;

use Calisero\Sms\Dto\Message;
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
    }
}
