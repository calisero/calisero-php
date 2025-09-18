<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Auth;

use Calisero\Sms\Auth\BearerTokenAuthProvider;
use PHPUnit\Framework\TestCase;

class BearerTokenAuthProviderTest extends TestCase
{
    public function testCanGetToken(): void
    {
        $token = 'test-bearer-token-123';
        $provider = new BearerTokenAuthProvider($token);

        $this->assertSame($token, $provider->getToken());
    }
}
