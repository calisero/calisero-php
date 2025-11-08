<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Verification DTO.
 */
class Verification
{
    private string $id;
    private string $phone;
    private ?string $brand;
    private string $status;
    private ?string $template;
    private string $createdAt;
    private string $expiresAt;
    private ?string $verifiedAt;
    private int $attempts;
    private bool $expired;
    
    public function __construct(
        string  $id,
        string  $phone,
        ?string $brand,
        string  $status,
        ?string $template,
        string  $createdAt,
        string  $expiresAt,
        ?string $verifiedAt,
        int     $attempts,
        bool    $expired
    )
    {
        $this->id         = $id;
        $this->phone      = $phone;
        $this->brand      = $brand;
        $this->status     = $status;
        $this->template   = $template;
        $this->createdAt  = $createdAt;
        $this->expiresAt  = $expiresAt;
        $this->verifiedAt = $verifiedAt;
        $this->attempts   = $attempts;
        $this->expired    = $expired;
    }
    
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        \assert(\is_string($data['id']));
        \assert(\is_string($data['phone']));
        \assert(\is_string($data['status']));
        \assert(\is_string($data['created_at']));
        \assert(\is_string($data['expires_at']));
        \assert(\is_int($data['attempts']));
        \assert(\is_bool($data['expired']));
        
        return new self(
            $data['id'],
            $data['phone'],
            isset($data['brand']) && \is_string($data['brand']) ? $data['brand'] : null,
            $data['status'],
            isset($data['template']) && \is_string($data['template']) ? $data['template'] : null,
            $data['created_at'],
            $data['expires_at'],
            isset($data['verified_at']) && \is_string($data['verified_at']) ? $data['verified_at'] : null,
            $data['attempts'],
            $data['expired']
        );
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getPhone(): string
    {
        return $this->phone;
    }
    
    public function getBrand(): ?string
    {
        return $this->brand;
    }
    
    public function getStatus(): string
    {
        return $this->status;
    }
    
    public function getTemplate(): ?string
    {
        return $this->template;
    }
    
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
    
    public function getExpiresAt(): string
    {
        return $this->expiresAt;
    }
    
    public function getVerifiedAt(): ?string
    {
        return $this->verifiedAt;
    }
    
    public function getAttempts(): int
    {
        return $this->attempts;
    }
    
    public function isExpired(): bool
    {
        return $this->expired;
    }
}
