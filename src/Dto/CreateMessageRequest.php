<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Request DTO for creating a new message.
 */
class CreateMessageRequest
{
    private string $recipient;
    private string $body;
    private ?string $visibleBody;
    private ?int $validity;
    private ?string $scheduleAt;
    private ?string $callbackUrl;
    private ?string $sender;

    public function __construct(
        string $recipient,
        string $body,
        ?string $visibleBody = null,
        ?int $validity = null,
        ?string $scheduleAt = null,
        ?string $callbackUrl = null,
        ?string $sender = null
    ) {
        $this->recipient = $recipient;
        $this->body = $body;
        $this->visibleBody = $visibleBody;
        $this->validity = $validity;
        $this->scheduleAt = $scheduleAt;
        $this->callbackUrl = $callbackUrl;
        $this->sender = $sender;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getVisibleBody(): ?string
    {
        return $this->visibleBody;
    }

    public function getValidity(): ?int
    {
        return $this->validity;
    }

    public function getScheduleAt(): ?string
    {
        return $this->scheduleAt;
    }

    public function getCallbackUrl(): ?string
    {
        return $this->callbackUrl;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    /**
     * Convert the request to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'recipient' => $this->recipient,
            'body' => $this->body,
        ];

        if ($this->visibleBody !== null) {
            $data['visible_body'] = $this->visibleBody;
        }

        if ($this->validity !== null) {
            $data['validity'] = $this->validity;
        }

        if ($this->scheduleAt !== null) {
            $data['schedule_at'] = $this->scheduleAt;
        }

        if ($this->callbackUrl !== null) {
            $data['callback_url'] = $this->callbackUrl;
        }

        if ($this->sender !== null) {
            $data['sender'] = $this->sender;
        }

        return $data;
    }
}
