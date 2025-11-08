<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Request DTO for checking a verification code (OTP).
 */
class VerificationCheckRequest
{
    private string $phone;
    private string $code;
    
    public function __construct(string $phone, string $code)
    {
        $this->phone = $phone;
        $this->code  = $code;
    }
    
    public function getPhone(): string
    {
        return $this->phone;
    }
    
    public function getCode(): string
    {
        return $this->code;
    }
    
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'phone' => $this->phone,
            'code'  => $this->code,
        ];
    }
}
