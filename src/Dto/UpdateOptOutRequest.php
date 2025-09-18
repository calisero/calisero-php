<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Request DTO for updating an opt-out.
 */
class UpdateOptOutRequest
{
    private string $phone;
    private ?string $reason;

    public function __construct(string $phone, ?string $reason = null)
    {
        $this->phone = $phone;
        $this->reason = $reason;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Convert the request to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'phone' => $this->phone,
        ];

        if ($this->reason !== null) {
            $data['reason'] = $this->reason;
        }

        return $data;
    }
}
