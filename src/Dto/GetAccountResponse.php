<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Response DTO for getting an account.
 */
class GetAccountResponse
{
    private Account $data;

    public function __construct(Account $data)
    {
        $this->data = $data;
    }

    /**
     * Create a GetAccountResponse instance from API response.
     *
     * @param array<string, mixed> $response
     */
    public static function fromArray(array $response): self
    {
        \assert(\is_array($response['data']));

        return new self(Account::fromArray($response['data']));
    }

    public function getData(): Account
    {
        return $this->data;
    }
}
