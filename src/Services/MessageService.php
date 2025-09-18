<?php

declare(strict_types=1);

namespace Calisero\Sms\Services;

use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Dto\CreateMessageResponse;
use Calisero\Sms\Dto\GetMessageResponse;
use Calisero\Sms\Dto\PaginatedMessages;
use Calisero\Sms\Http\HttpClient;

/**
 * Service for managing SMS messages.
 */
class MessageService
{
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Create a new SMS message.
     */
    public function create(CreateMessageRequest $request): CreateMessageResponse
    {
        $response = $this->httpClient->post('/messages', $request->toArray(), true);

        return CreateMessageResponse::fromArray($response);
    }

    /**
     * Retrieve a specific message by ID.
     */
    public function get(string $messageId): GetMessageResponse
    {
        $response = $this->httpClient->get("/messages/{$messageId}");

        return GetMessageResponse::fromArray($response);
    }

    /**
     * List messages with pagination.
     */
    public function list(int $page = 1): PaginatedMessages
    {
        $queryParams = [];
        if ($page > 1) {
            $queryParams['page'] = $page;
        }

        $response = $this->httpClient->get('/messages', $queryParams);

        return PaginatedMessages::fromArray($response);
    }

    /**
     * Delete a message (only if not yet sent).
     */
    public function delete(string $messageId): void
    {
        $this->httpClient->delete("/messages/{$messageId}");
    }
}
