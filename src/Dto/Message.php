<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Message DTO.
 */
class Message
{
    private string $id;
    private string $recipient;
    private string $body;
    private int $parts;
    private string $createdAt;
    private ?string $scheduledAt;
    private ?string $sentAt;
    private ?string $deliveredAt;
    private ?string $callbackUrl;
    private string $status;
    private ?string $sender;

    public function __construct(
        string $id,
        string $recipient,
        string $body,
        int $parts,
        string $createdAt,
        ?string $scheduledAt,
        ?string $sentAt,
        ?string $deliveredAt,
        ?string $callbackUrl,
        string $status,
        ?string $sender
    ) {
        $this->id = $id;
        $this->recipient = $recipient;
        $this->body = $body;
        $this->parts = $parts;
        $this->createdAt = $createdAt;
        $this->scheduledAt = $scheduledAt;
        $this->sentAt = $sentAt;
        $this->deliveredAt = $deliveredAt;
        $this->callbackUrl = $callbackUrl;
        $this->status = $status;
        $this->sender = $sender;
    }

    /**
     * Create a Message instance from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        \assert(\is_string($data['id']));
        \assert(\is_string($data['recipient']));
        \assert(\is_string($data['body']));
        \assert(\is_int($data['parts']));
        \assert(\is_string($data['created_at']));
        \assert(\is_string($data['status']));

        return new self(
            $data['id'],
            $data['recipient'],
            $data['body'],
            $data['parts'],
            $data['created_at'],
            isset($data['scheduled_at']) && \is_string($data['scheduled_at']) ? $data['scheduled_at'] : null,
            isset($data['sent_at']) && \is_string($data['sent_at']) ? $data['sent_at'] : null,
            isset($data['delivered_at']) && \is_string($data['delivered_at']) ? $data['delivered_at'] : null,
            isset($data['callback_url']) && \is_string($data['callback_url']) ? $data['callback_url'] : null,
            $data['status'],
            isset($data['sender']) && \is_string($data['sender']) ? $data['sender'] : null
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getParts(): int
    {
        return $this->parts;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getScheduledAt(): ?string
    {
        return $this->scheduledAt;
    }

    public function getSentAt(): ?string
    {
        return $this->sentAt;
    }

    public function getDeliveredAt(): ?string
    {
        return $this->deliveredAt;
    }

    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }
}
