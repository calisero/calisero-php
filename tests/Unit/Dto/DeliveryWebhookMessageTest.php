<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Dto;

use Calisero\Sms\Dto\DeliveryWebhookMessage;
use PHPUnit\Framework\TestCase;

class DeliveryWebhookMessageTest extends TestCase
{
    public function testCanCreateFromJson(): void
    {
        $webhook = DeliveryWebhookMessage::fromJson((string) \json_encode([
            'price' => 0.0378,
            'sender' => 'CALISERO',
            'sentAt' => '2025-09-19T12:01:15.000000Z',
            'status' => 'delivered',
            'messageId' => '019961db-14a7-7348-963f-5a7a789a969f',
            'recipient' => '+40742**350',
            'scheduleAt' => '2025-09-19T12:02:51.000000Z',
            'deliveredAt' => '2025-09-19T12:02:53.000000Z',
            'remainingBalance' => 999.392,
            'dailyLimit' => 1000,
            'dailyRemaining' => 588,
            'sentToday' => 412,
        ]));

        $this->assertSame('019961db-14a7-7348-963f-5a7a789a969f', $webhook->getMessageId());
        $this->assertSame('CALISERO', $webhook->getSender());
        $this->assertSame('+40742**350', $webhook->getRecipient());
        $this->assertSame('delivered', $webhook->getStatus());
        $this->assertSame('2025-09-19T12:02:51.000000Z', $webhook->getScheduleAt());
        $this->assertSame('2025-09-19T12:01:15.000000Z', $webhook->getSentAt());
        $this->assertSame('2025-09-19T12:02:53.000000Z', $webhook->getDeliveredAt());
        $this->assertSame(0.0378, $webhook->getPrice());
        $this->assertSame(999.392, $webhook->getRemainingBalance());
        $this->assertSame(1000, $webhook->getDailyLimit());
        $this->assertSame(588, $webhook->getDailyRemaining());
        $this->assertSame(412, $webhook->getSentToday());
    }

    public function testMinimalPayload(): void
    {
        // A message sent under the default sender, by an account without a daily limit.
        $webhook = DeliveryWebhookMessage::fromArray([
            'messageId' => '019961db-14a7-7348-963f-5a7a789a969f',
            'sender' => null,
            'recipient' => '+40742**350',
            'status' => 'sent',
            'scheduleAt' => null,
            'sentAt' => '2025-09-19T12:01:15.000000Z',
            'deliveredAt' => null,
            'price' => 0,
            'remainingBalance' => null,
            'dailyLimit' => null,
            'dailyRemaining' => null,
            'sentToday' => 3,
        ]);

        $this->assertNull($webhook->getSender());
        $this->assertSame('sent', $webhook->getStatus());
        $this->assertNull($webhook->getScheduleAt());
        $this->assertNull($webhook->getDeliveredAt());
        $this->assertSame(0.0, $webhook->getPrice());
        $this->assertNull($webhook->getRemainingBalance());
        $this->assertNull($webhook->getDailyLimit());
        $this->assertNull($webhook->getDailyRemaining());
        $this->assertSame(3, $webhook->getSentToday());
    }

    public function testPayloadThatPredatesTheDailyLimit(): void
    {
        $webhook = DeliveryWebhookMessage::fromArray([
            'messageId' => '019961db-14a7-7348-963f-5a7a789a969f',
            'sender' => 'CALISERO',
            'recipient' => '+40742**350',
            'status' => 'undelivered',
            'price' => 0.0378,
        ]);

        $this->assertSame('undelivered', $webhook->getStatus());
        $this->assertNull($webhook->getSentAt());
        $this->assertNull($webhook->getSentToday());
    }

    public function testPriceSentAsANumericString(): void
    {
        $webhook = DeliveryWebhookMessage::fromArray([
            'messageId' => '019961db-14a7-7348-963f-5a7a789a969f',
            'recipient' => '+40742**350',
            'status' => 'delivered',
            'price' => '0.0378',
            'remainingBalance' => '999.392',
        ]);

        $this->assertSame(0.0378, $webhook->getPrice());
        $this->assertSame(999.392, $webhook->getRemainingBalance());
    }

    public function testInvalidJsonIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid delivery webhook payload');

        DeliveryWebhookMessage::fromJson('{"messageId":');
    }

    public function testJsonThatIsNotAnObjectIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('expected a JSON object');

        DeliveryWebhookMessage::fromJson('"delivered"');
    }

    public function testMissingRequiredFieldIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"messageId" must be a non-empty string');

        DeliveryWebhookMessage::fromArray([
            'recipient' => '+40742**350',
            'status' => 'delivered',
            'price' => 0.0378,
        ]);
    }

    public function testMissingPriceIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"price" must be a number');

        DeliveryWebhookMessage::fromArray([
            'messageId' => '019961db-14a7-7348-963f-5a7a789a969f',
            'recipient' => '+40742**350',
            'status' => 'delivered',
        ]);
    }
}
