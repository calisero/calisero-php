<?php

declare(strict_types=1);

namespace Calisero\Sms\Services;

use Calisero\Sms\Dto\CreateOptOutRequest;
use Calisero\Sms\Dto\CreateOptOutResponse;
use Calisero\Sms\Dto\GetOptOutResponse;
use Calisero\Sms\Dto\PaginatedOptOuts;
use Calisero\Sms\Dto\UpdateOptOutRequest;
use Calisero\Sms\Http\HttpClient;

/**
 * Service for managing SMS opt-outs.
 */
class OptOutService
{
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Create a new opt-out.
     */
    public function create(CreateOptOutRequest $request): CreateOptOutResponse
    {
        $response = $this->httpClient->post('/opt-outs', $request->toArray());

        return CreateOptOutResponse::fromArray($response);
    }

    /**
     * Retrieve a specific opt-out by ID.
     */
    public function get(string $optOutId): GetOptOutResponse
    {
        $response = $this->httpClient->get("/opt-outs/{$optOutId}");

        return GetOptOutResponse::fromArray($response);
    }

    /**
     * List opt-outs with pagination.
     */
    public function list(int $page = 1): PaginatedOptOuts
    {
        $queryParams = [];
        if ($page > 1) {
            $queryParams['page'] = $page;
        }

        $response = $this->httpClient->get('/opt-outs', $queryParams);

        return PaginatedOptOuts::fromArray($response);
    }

    /**
     * Update an opt-out.
     */
    public function update(string $optOutId, UpdateOptOutRequest $request): GetOptOutResponse
    {
        $response = $this->httpClient->put("/opt-outs/{$optOutId}", $request->toArray());

        return GetOptOutResponse::fromArray($response);
    }

    /**
     * Delete an opt-out.
     */
    public function delete(string $optOutId): void
    {
        $this->httpClient->delete("/opt-outs/{$optOutId}");
    }
}
