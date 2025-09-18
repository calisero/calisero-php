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
