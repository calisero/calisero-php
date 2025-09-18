<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Response DTO for getting a message.
 */
class GetMessageResponse
{
    private Message $data;

    public function __construct(Message $data)
    {
        $this->data = $data;
    }

    /**
     * Create a GetMessageResponse instance from API response.
     *
     * @param array<string, mixed> $response
     */
    public static function fromArray(array $response): self
    {
        \assert(\is_array($response['data']));

        return new self(Message::fromArray($response['data']));
    }

    public function getData(): Message
    {
        return $this->data;
    }
}
