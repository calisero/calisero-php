<?php

declare(strict_types=1);

namespace Calisero\Sms\Contracts;

/**
 * Interface for providing authentication tokens.
 */
interface AuthProviderInterface
{
    /**
     * Get the bearer token for API authentication.
     */
    public function getToken(): string;
}
