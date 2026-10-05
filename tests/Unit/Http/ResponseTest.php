<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Http;

use Calisero\Sms\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testHeaderNamesMatchCaseInsensitively(): void
    {
        // cURL keeps the server's spelling: mixed case over HTTP/1.1, lowercase over HTTP/2.
        $response = new Response(200, ['Retry-After' => ['60'], 'x-trace-id' => ['abc']], '');

        $this->assertSame(['60'], $response->getHeader('retry-after'));
        $this->assertSame(['60'], $response->getHeader('RETRY-AFTER'));
        $this->assertSame(['abc'], $response->getHeader('X-Trace-Id'));
        $this->assertSame('abc', $response->getHeaderLine('X-Trace-Id'));
    }

    public function testValuesOfDifferentlySpelledNamesAreMerged(): void
    {
        $response = new Response(200, ['Set-Cookie' => ['a=1'], 'set-cookie' => ['b=2']], '');

        $this->assertSame(['a=1', 'b=2'], $response->getHeader('SET-COOKIE'));
        $this->assertSame('a=1, b=2', $response->getHeaderLine('set-cookie'));
    }

    public function testMissingHeader(): void
    {
        $response = new Response(200, [], '');

        $this->assertSame([], $response->getHeader('X-Trace-Id'));
        $this->assertSame('', $response->getHeaderLine('X-Trace-Id'));
    }
}
