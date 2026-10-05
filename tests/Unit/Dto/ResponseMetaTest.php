<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Dto;

use Calisero\Sms\Dto\ResponseMeta;
use Calisero\Sms\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseMetaTest extends TestCase
{
    public function testReadsTheHeadersOfACreatedMessage(): void
    {
        $meta = ResponseMeta::fromResponse(new Response(201, [
            'X-Trace-Id' => ['9b80eef1-49d4-4502-85a8-febb68cc11a7'],
            'X-RateLimit-Limit' => ['240'],
            'X-RateLimit-Remaining' => ['239'],
            'X-Daily-Limit' => ['1000'],
            'X-Daily-Remaining' => ['873'],
        ], '{}'));

        $this->assertSame('9b80eef1-49d4-4502-85a8-febb68cc11a7', $meta->getTraceId());
        $this->assertSame(240, $meta->getRateLimitLimit());
        $this->assertSame(239, $meta->getRateLimitRemaining());
        $this->assertSame(1000, $meta->getDailyLimit());
        $this->assertSame(873, $meta->getDailyRemaining());
    }

    public function testHeaderNamesMatchWhateverTheirSpelling(): void
    {
        $meta = ResponseMeta::fromResponse(new Response(201, [
            'x-daily-limit' => ['1000'],
            'x-daily-remaining' => ['0'],
        ], '{}'));

        $this->assertSame(1000, $meta->getDailyLimit());
        $this->assertSame(0, $meta->getDailyRemaining());
    }

    public function testAccountWithoutADailyLimit(): void
    {
        // The API sends X-Daily-Limit and X-Daily-Remaining only while the account has a limit.
        $meta = ResponseMeta::fromResponse(new Response(201, [
            'X-RateLimit-Limit' => ['240'],
            'X-RateLimit-Remaining' => ['200'],
        ], '{}'));

        $this->assertNull($meta->getDailyLimit());
        $this->assertNull($meta->getDailyRemaining());
        $this->assertSame(200, $meta->getRateLimitRemaining());
    }

    public function testValuesThatAreNotNumbersAreIgnored(): void
    {
        $meta = ResponseMeta::fromResponse(new Response(201, [
            'X-Daily-Limit' => ['unlimited'],
            'X-RateLimit-Remaining' => [''],
        ], '{}'));

        $this->assertNull($meta->getDailyLimit());
        $this->assertNull($meta->getRateLimitRemaining());
    }

    public function testNoResponse(): void
    {
        $meta = ResponseMeta::fromResponse(null);

        $this->assertNull($meta->getTraceId());
        $this->assertNull($meta->getRateLimitLimit());
        $this->assertNull($meta->getRateLimitRemaining());
        $this->assertNull($meta->getDailyLimit());
        $this->assertNull($meta->getDailyRemaining());
    }
}
