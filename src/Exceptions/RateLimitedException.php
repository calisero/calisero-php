<?php

declare(strict_types=1);

namespace Calisero\Sms\Exceptions;

/**
 * Exception thrown when rate limit is exceeded (429).
 */
class RateLimitedException extends ApiException
{
    private ?int $retryAfter;

    /**
     * @param array<string, mixed> $errorDetails
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        ?int $statusCode = null,
        ?string $requestId = null,
        array $errorDetails = [],
        ?int $retryAfter = null
    ) {
        parent::__construct($message, $code, $previous, $statusCode, $requestId, $errorDetails);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
