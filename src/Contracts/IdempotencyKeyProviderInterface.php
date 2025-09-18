<?php

declare(strict_types=1);

namespace Calisero\Sms\Contracts;

/**
 * Interface for generating idempotency keys.
 */
interface IdempotencyKeyProviderInterface
{
    /**
     * Generate a unique idempotency key.
     */
    public function generate(): string;
}
