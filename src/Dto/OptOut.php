<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Opt-out DTO.
 */
class OptOut
{
    private string $id;
    private string $phone;
    private ?string $reason;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        string $id,
        string $phone,
        ?string $reason,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->phone = $phone;
        $this->reason = $reason;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Create an OptOut instance from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        \assert(\is_string($data['id']));
        \assert(\is_string($data['phone']));
        \assert(\is_string($data['created_at']));
        \assert(\is_string($data['updated_at']));

        return new self(
            $data['id'],
            $data['phone'],
            isset($data['reason']) && \is_string($data['reason']) ? $data['reason'] : null,
            $data['created_at'],
            $data['updated_at']
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

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }
}
