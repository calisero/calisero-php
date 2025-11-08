<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Response DTO for getting a verification.
 */
class GetVerificationResponse
{
    private Verification $data;

    public function __construct(Verification $data)
    {
        $this->data = $data;
    }

    /**
     * @param array<string, mixed> $response
     */
    public static function fromArray(array $response): self
    {
        \assert(\is_array($response['data']));

        return new self(Verification::fromArray($response['data']));
    }

    public function getData(): Verification
    {
        return $this->data;
    }
}
