<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Dto;

use Calisero\Sms\Dto\CreateMessageRequest;
use PHPUnit\Framework\TestCase;

class CreateMessageRequestTest extends TestCase
{
    public function testCanCreateMinimalRequest(): void
    {
        $request = new CreateMessageRequest(
            '+40742***350',
            'Test message'
        );

        $this->assertSame('+40742***350', $request->getRecipient());
        $this->assertSame('Test message', $request->getBody());
        $this->assertNull($request->getVisibleBody());
        $this->assertNull($request->getValidity());
        $this->assertNull($request->getScheduleAt());
        $this->assertNull($request->getCallbackUrl());
        $this->assertNull($request->getSender());
        $this->assertNull($request->getShortenUrls());
    }

    public function testCanCreateFullRequest(): void
    {
        $request = new CreateMessageRequest(
            '+40742***350',
            'Test message',
            'Visible test message',
            24,
            '2024-12-25 10:00:00',
            'https://example.com/webhook',
            'TestSender'
        );

        $this->assertSame('+40742***350', $request->getRecipient());
        $this->assertSame('Test message', $request->getBody());
        $this->assertSame('Visible test message', $request->getVisibleBody());
        $this->assertSame(24, $request->getValidity());
        $this->assertSame('2024-12-25 10:00:00', $request->getScheduleAt());
        $this->assertSame('https://example.com/webhook', $request->getCallbackUrl());
        $this->assertSame('TestSender', $request->getSender());
    }

    public function testToArrayWithMinimalData(): void
    {
        $request = new CreateMessageRequest(
            '+40742***350',
            'Test message'
        );

        $expected = [
            'recipient' => '+40742***350',
            'body' => 'Test message',
        ];

        $this->assertSame($expected, $request->toArray());
    }

    public function testToArrayWithFullData(): void
    {
        $request = new CreateMessageRequest(
            '+40742***350',
            'Test message',
            'Visible test message',
            24,
            '2024-12-25 10:00:00',
            'https://example.com/webhook',
            'TestSender'
        );

        $expected = [
            'recipient' => '+40742***350',
            'body' => 'Test message',
            'visible_body' => 'Visible test message',
            'validity' => 24,
            'schedule_at' => '2024-12-25 10:00:00',
            'callback_url' => 'https://example.com/webhook',
            'sender' => 'TestSender',
        ];

        $this->assertSame($expected, $request->toArray());
    }

    public function testToArrayWithShortenUrls(): void
    {
        $request = new CreateMessageRequest(
            '+40742***350',
            'Track your order: https://example.com/orders/12345',
            null,
            null,
            null,
            null,
            null,
            true
        );

        $this->assertTrue($request->getShortenUrls());
        $this->assertSame([
            'recipient' => '+40742***350',
            'body' => 'Track your order: https://example.com/orders/12345',
            'shorten_urls' => true,
        ], $request->toArray());
    }

    public function testToArrayKeepsShortenUrlsFalse(): void
    {
        $request = new CreateMessageRequest(
            '+40742***350',
            'Track your order: https://example.com/orders/12345',
            null,
            null,
            null,
            null,
            null,
            false
        );

        $this->assertFalse($request->getShortenUrls());
        $this->assertFalse($request->toArray()['shorten_urls']);
    }
}
