<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Response DTO for creating a verification.
 */
class CreateVerificationResponse
{
    private Verification $data;
    private ResponseMeta $responseMeta;

    public function __construct(Verification $data, ?ResponseMeta $responseMeta = null)
    {
        $this->data = $data;
        $this->responseMeta = $responseMeta ?? new ResponseMeta();
    }

    /**
     * @param array<string, mixed> $response
     */
    public static function fromArray(array $response): self
    {
        \assert(\is_array($response['data']));

        return new self(Verification::fromArray($response['data']));
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

    public function getData(): Verification
    {
        return $this->data;
    }

    /**
     * The answer's headers: trace id, request rate limit and what is left of the
     * account's daily sending limit after this code's SMS.
     */
    public function getResponseMeta(): ResponseMeta
    {
        return $this->responseMeta;
    }
}
