<?php

declare(strict_types=1);

namespace Calisero\Sms\Exceptions;

/**
 * Exception thrown when rate limit is exceeded (429).
 *
 * This is the request rate limit (each API user may make 240 requests a minute);
 * a refusal of the account's daily sending limit is thrown as the subclass
 * {@see DailyLimitExceededException}.
 */
class RateLimitedException extends ApiException
{
    private ?int $retryAfter;
    private ?int $rateLimitLimit;
    private ?int $rateLimitRemaining;
    private ?int $rateLimitReset;

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
        ?int $retryAfter = null,
        ?int $rateLimitLimit = null,
        ?int $rateLimitRemaining = null,
        ?int $rateLimitReset = null
    ) {
        parent::__construct($message, $code, $previous, $statusCode, $requestId, $errorDetails);
        $this->retryAfter = $retryAfter;
        $this->rateLimitLimit = $rateLimitLimit;
        $this->rateLimitRemaining = $rateLimitRemaining;
        $this->rateLimitReset = $rateLimitReset;
    }

    /**
     * Seconds to wait before sending again (the Retry-After header).
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    /**
     * How many requests the API user may make a minute (the X-RateLimit-Limit header).
     */
    public function getRateLimitLimit(): ?int
    {
        return $this->rateLimitLimit;
    }

    /**
     * How many requests the API user may still make this minute (the
     * X-RateLimit-Remaining header): 0 when the request rate limit refused the request.
     */
    public function getRateLimitRemaining(): ?int
    {
        return $this->rateLimitRemaining;
    }

    /**
     * When requests are accepted again, as a Unix timestamp (the X-RateLimit-Reset
     * header); sent only when the request rate limit refused the request.
     */
    public function getRateLimitReset(): ?int
    {
        return $this->rateLimitReset;
    }
}
