<?php

declare(strict_types=1);

namespace Calisero\Sms\Exceptions;

/**
 * Exception thrown when the account reached its daily sending limit (429 with the
 * code daily_limit_exceeded): nothing was sent and nothing was billed.
 *
 * The limit resets at midnight, Romania time ({@see getResetsAt()}); test messages
 * never count towards it. It extends RateLimitedException, so code that catches
 * that one keeps catching this refusal too.
 */
class DailyLimitExceededException extends RateLimitedException
{
    /**
     * The `code` of the error body that tells this refusal from the request rate limit's.
     */
    public const ERROR_CODE = 'daily_limit_exceeded';

    private ?int $dailyLimit;
    private ?int $dailyRemaining;
    private ?string $resetsAt;

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
        ?int $rateLimitReset = null,
        ?int $dailyLimit = null,
        ?int $dailyRemaining = null,
        ?string $resetsAt = null
    ) {
        parent::__construct(
            $message,
            $code,
            $previous,
            $statusCode,
            $requestId,
            $errorDetails,
            $retryAfter,
            $rateLimitLimit,
            $rateLimitRemaining,
            $rateLimitReset
        );
        $this->dailyLimit = $dailyLimit;
        $this->dailyRemaining = $dailyRemaining;
        $this->resetsAt = $resetsAt;
    }

    /**
     * How many messages the account can send in a day.
     */
    public function getDailyLimit(): ?int
    {
        return $this->dailyLimit;
    }

    /**
     * How many messages the account can still send today: 0 once the limit is reached.
     */
    public function getDailyRemaining(): ?int
    {
        return $this->dailyRemaining;
    }

    /**
     * The next midnight, Romania time, when the account can send again, as an
     * ISO 8601 date-time (e.g. 2026-10-01T00:00:00+03:00).
     */
    public function getResetsAt(): ?string
    {
        return $this->resetsAt;
    }
}
