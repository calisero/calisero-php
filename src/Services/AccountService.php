<?php

declare(strict_types=1);

namespace Calisero\Sms\Services;

use Calisero\Sms\Dto\GetAccountResponse;
use Calisero\Sms\Http\HttpClient;

/**
 * Service for managing account information.
 */
class AccountService
{
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Retrieve account information by ID.
     */
    public function get(string $accountId): GetAccountResponse
    {
        $response = $this->httpClient->get("/accounts/{$accountId}");

        return GetAccountResponse::fromArray($response);
    }
}
