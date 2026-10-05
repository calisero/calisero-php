<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

use Calisero\Sms\Http\HeaderReader;
use Calisero\Sms\Http\ResponseInterface;

/**
 * What the API reports in the headers of an answer: the request's trace id, the
 * request rate limit and, for a created message or verification, the account's
 * daily sending limit. A value is null when the answer did not carry it.
 */
class ResponseMeta
{
    private ?string $traceId;
    private ?int $rateLimitLimit;
    private ?int $rateLimitRemaining;
    private ?int $dailyLimit;
    private ?int $dailyRemaining;

    public function __construct(
        ?string $traceId = null,
        ?int $rateLimitLimit = null,
        ?int $rateLimitRemaining = null,
        ?int $dailyLimit = null,
        ?int $dailyRemaining = null
    ) {
        $this->traceId = $traceId;
        $this->rateLimitLimit = $rateLimitLimit;
        $this->rateLimitRemaining = $rateLimitRemaining;
        $this->dailyLimit = $dailyLimit;
        $this->dailyRemaining = $dailyRemaining;
    }

    /**
     * Read the headers of an answer; every value is null when there is no answer.
     */
    public static function fromResponse(?ResponseInterface $response): self
    {
        if ($response === null) {
            return new self();
        }

        return new self(
            HeaderReader::string($response, 'X-Trace-Id'),
            HeaderReader::int($response, 'X-RateLimit-Limit'),
            HeaderReader::int($response, 'X-RateLimit-Remaining'),
            HeaderReader::int($response, 'X-Daily-Limit'),
            HeaderReader::int($response, 'X-Daily-Remaining')
        );
    }

    /**
     * The request's trace id (X-Trace-Id): quote it to Calisero support, or look the
     * request up in the dashboard under Developers → Debug.
     */
    public function getTraceId(): ?string
    {
        return $this->traceId;
    }

    /**
     * How many requests the API user may make a minute (X-RateLimit-Limit).
     */
    public function getRateLimitLimit(): ?int
    {
        return $this->rateLimitLimit;
    }

    /**
     * How many requests the API user may still make this minute (X-RateLimit-Remaining).
     */
    public function getRateLimitRemaining(): ?int
    {
        return $this->rateLimitRemaining;
    }

    /**
     * How many messages the account can send in a day (X-Daily-Limit); null when
     * no daily limit applies.
     */
    public function getDailyLimit(): ?int
    {
        return $this->dailyLimit;
    }

    /**
     * How many messages the account can still send today, after this one
     * (X-Daily-Remaining); null when no daily limit applies.
     */
    public function getDailyRemaining(): ?int
    {
        return $this->dailyRemaining;
    }
}
