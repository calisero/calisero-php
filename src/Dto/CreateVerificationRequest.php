<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Request DTO for creating a verification.
 */
class CreateVerificationRequest
{
    private string $phone;
    private ?string $brand;
    private ?string $template;
    private ?int $expiresIn;

    public function __construct(string $phone, ?string $brand = null, ?string $template = null, ?int $expiresIn = null)
    {
        $this->phone = $phone;
        $this->brand = $brand;
        $this->template = $template;
        $this->expiresIn = $expiresIn;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getExpiresIn(): ?int
    {
        return $this->expiresIn;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = ['phone' => $this->phone];
        if ($this->brand !== null) {
            $data['brand'] = $this->brand;
        }
        if ($this->template !== null) {
            $data['template'] = $this->template;
        }
        if ($this->expiresIn !== null) {
            $data['expires_in'] = $this->expiresIn;
        }

        return $data;
    }
}
