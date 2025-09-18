<?php

declare(strict_types=1);

namespace Calisero\Sms\Auth;

use Calisero\Sms\Contracts\AuthProviderInterface;

/**
 * Simple bearer token authentication provider.
 */
class BearerTokenAuthProvider implements AuthProviderInterface
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
