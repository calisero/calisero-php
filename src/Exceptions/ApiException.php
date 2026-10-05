<?php

declare(strict_types=1);

namespace Calisero\Sms\Exceptions;

use Exception;

/**
 * Base exception for all SMS API related errors.
 */
class ApiException extends \Exception
{
    /**
     * @var array<string, mixed>
     */
    protected array $errorDetails;
    private ?string $requestId;
    private ?int $statusCode;

    /**
     * @param ?string              $requestId    the request's trace id
     * @param array<string, mixed> $errorDetails
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        ?int $statusCode = null,
        ?string $requestId = null,
        array $errorDetails = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
        $this->requestId = $requestId;
        $this->errorDetails = $errorDetails;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * The request's trace id, from the X-Trace-Id header or the error body's
     * trace_id: quote it to Calisero support, or look the request up in the
     * dashboard under Developers → Debug.
     */
    public function getTraceId(): ?string
    {
        return $this->requestId;
    }

    /**
     * The request's trace id, the same as {@see getTraceId()}.
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }
}
