<?php

declare(strict_types=1);

namespace Calisero\Sms\Services;

use Calisero\Sms\Dto\CreateVerificationRequest;
use Calisero\Sms\Dto\CreateVerificationResponse;
use Calisero\Sms\Dto\GetVerificationResponse;
use Calisero\Sms\Dto\PaginatedVerifications;
use Calisero\Sms\Dto\VerificationCheckRequest;
use Calisero\Sms\Http\HttpClient;

/**
 * Service for managing phone verifications (OTP).
 */
class VerificationService
{
    private HttpClient $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * List verifications with optional status filter and pagination.
     */
    public function list(int $page = 1, ?string $status = null): PaginatedVerifications
    {
        $query = [];
        if ($page > 1) {
            $query['page'] = $page;
        }
        if ($status !== null) {
            $query['status'] = $status;
        }

        $response = $this->httpClient->get('/verifications', $query);

        return PaginatedVerifications::fromArray($response);
    }

    /**
     * Create a new verification request (send OTP).
     */
    public function create(CreateVerificationRequest $request): CreateVerificationResponse
    {
        $response = $this->httpClient->post('/verifications', $request->toArray(), true);

        return CreateVerificationResponse::fromArray($response);
    }

    /**
     * Retrieve a verification by id.
     */
    public function get(string $verificationId): GetVerificationResponse
    {
        $response = $this->httpClient->get("/verifications/{$verificationId}");

        return GetVerificationResponse::fromArray($response);
    }

    /**
     * Validate a verification code (OTP) for a phone number.
     */
    public function validate(VerificationCheckRequest $request): GetVerificationResponse
    {
        $response = $this->httpClient->put('/verifications/validate', $request->toArray());

        return GetVerificationResponse::fromArray($response);
    }
}
