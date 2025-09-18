<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Response DTO for getting an opt-out.
 */
class GetOptOutResponse
{
    private OptOut $data;

    public function __construct(OptOut $data)
    {
        $this->data = $data;
    }

    /**
     * Create a GetOptOutResponse instance from API response.
     *
     * @param array<string, mixed> $response
     */
    public static function fromArray(array $response): self
    {
        \assert(\is_array($response['data']));

        return new self(OptOut::fromArray($response['data']));
    }

    public function getData(): OptOut
    {
        return $this->data;
    }
}
