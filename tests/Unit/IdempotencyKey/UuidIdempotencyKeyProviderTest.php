<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\IdempotencyKey;

use Calisero\Sms\IdempotencyKey\UuidIdempotencyKeyProvider;
use PHPUnit\Framework\TestCase;

class UuidIdempotencyKeyProviderTest extends TestCase
{
    public function testGeneratesValidUuid(): void
    {
        $provider = new UuidIdempotencyKeyProvider();
        $uuid = $provider->generate();

        // Test UUID format: 8-4-4-4-12 hex digits
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    public function testGeneratesDifferentUuids(): void
    {
        $provider = new UuidIdempotencyKeyProvider();

        $uuid1 = $provider->generate();
        $uuid2 = $provider->generate();

        $this->assertNotSame($uuid1, $uuid2);
    }
}
