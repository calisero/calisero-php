<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Response DTO for message creation.
 */
class CreateMessageResponse
{
    private Message $data;
    private ResponseMeta $responseMeta;

    public function __construct(Message $data, ?ResponseMeta $responseMeta = null)
    {
        $this->data = $data;
        $this->responseMeta = $responseMeta ?? new ResponseMeta();
    }

    /**
     * Create a CreateMessageResponse instance from API response.
     *
     * @param array<string, mixed> $response
     */
    public static function fromArray(array $response): self
    {
        \assert(\is_array($response['data']));

        return new self(Message::fromArray($response['data']));
    }

    /**
     * A copy carrying the given response meta.
     */
    public function withResponseMeta(ResponseMeta $responseMeta): self
    {
        $clone = clone $this;
        $clone->responseMeta = $responseMeta;

        return $clone;
    }

    public function getData(): Message
    {
        return $this->data;
    }

    /**
     * The answer's headers: trace id, request rate limit and what is left of the
     * account's daily sending limit after this message.
     */
    public function getResponseMeta(): ResponseMeta
    {
        return $this->responseMeta;
    }
}
