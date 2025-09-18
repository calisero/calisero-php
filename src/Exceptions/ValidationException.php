<?php

declare(strict_types=1);

namespace Calisero\Sms\Exceptions;

/**
 * Exception thrown for validation errors (422).
 */
class ValidationException extends ApiException
{
    /**
     * @var array<string, mixed>
     */
    private array $validationErrors;

    /**
     * @param array<string, mixed> $errorDetails
     * @param array<string, mixed> $validationErrors
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        ?int $statusCode = null,
        ?string $requestId = null,
        array $errorDetails = [],
        array $validationErrors = []
    ) {
        parent::__construct($message, $code, $previous, $statusCode, $requestId, $errorDetails);
        $this->validationErrors = $validationErrors;
    }

    /**
     * @return array<string, mixed>
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
