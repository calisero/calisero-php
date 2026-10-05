<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Dto;

use Calisero\Sms\Dto\CreateMessageResponse;
use Calisero\Sms\Dto\CreateVerificationResponse;
use Calisero\Sms\Dto\ResponseMeta;
use PHPUnit\Framework\TestCase;

class CreateMessageResponseTest extends TestCase
{
    public function testWithResponseMetaReturnsACopy(): void
    {
        $response = CreateMessageResponse::fromArray([
            'data' => [
                'id' => '9e2574e8-3615-4090-9b5a-0fc812079da8',
                'recipient' => '+40742***350',
                'body' => 'Test message!',
                'parts' => 1,
                'created_at' => '2025-02-06T10:18:43.000000Z',
                'status' => 'scheduled',
            ],
        ]);
        $meta = new ResponseMeta('9b80eef1-49d4-4502-85a8-febb68cc11a7', 240, 239, 1000, 873);

        $withMeta = $response->withResponseMeta($meta);

        $this->assertSame($meta, $withMeta->getResponseMeta());
        $this->assertSame($response->getData(), $withMeta->getData());
        $this->assertNull($response->getResponseMeta()->getTraceId());
    }

    public function testFromArrayKeepsItsSignature(): void
    {
        // A subclass overriding fromArray(array $response) must keep loading.
        foreach ([CreateMessageResponse::class, CreateVerificationResponse::class] as $class) {
            $method = new \ReflectionMethod($class, 'fromArray');

            $this->assertSame(1, $method->getNumberOfParameters(), "{$class}::fromArray()");
        }
    }
}
