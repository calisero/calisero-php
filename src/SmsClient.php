<?php

declare(strict_types=1);

namespace Calisero\Sms;

use Calisero\Sms\Auth\BearerTokenAuthProvider;
use Calisero\Sms\Http\BaseHttpClient;
use Calisero\Sms\Http\Factory\HttpFactory;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\IdempotencyKey\UuidIdempotencyKeyProvider;
use Calisero\Sms\Services\AccountService;
use Calisero\Sms\Services\MessageService;
use Calisero\Sms\Services\OptOutService;
use Calisero\Sms\Services\VerificationService;

/**
 * Main SMS API client.
 */
class SmsClient
{
    private HttpClient $httpClient;
    private MessageService $messageService;
    private OptOutService $optOutService;
    private AccountService $accountService;
    private VerificationService $verificationService;

    private function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->messageService = new MessageService($this->httpClient);
        $this->optOutService = new OptOutService($this->httpClient);
        $this->accountService = new AccountService($this->httpClient);
        $this->verificationService = new VerificationService($this->httpClient);
    }

    /**
     * Create a client instance with a bearer token.
     *
     * @param string $bearerToken The API bearer token for authentication
     */
    public static function create(string $bearerToken): self
    {
        $baseHttpClient = new BaseHttpClient(30, 10);
        $httpFactory = new HttpFactory();
        $authProvider = new BearerTokenAuthProvider($bearerToken);
        $idempotencyKeyProvider = new UuidIdempotencyKeyProvider();

        $httpClient = new HttpClient(
            $baseHttpClient,
            $httpFactory,
            $authProvider,
            'https://rest.calisero.ro/api/v1',
            $idempotencyKeyProvider
        );

        return new self($httpClient);
    }

    /**
     * Get the message service.
     */
    public function messages(): MessageService
    {
        return $this->messageService;
    }

    /**
     * Get the opt-out service.
     */
    public function optOuts(): OptOutService
    {
        return $this->optOutService;
    }

    /**
     * Get the account service.
     */
    public function accounts(): AccountService
    {
        return $this->accountService;
    }

    /**
     * Get the verifications service.
     */
    public function verifications(): VerificationService
    {
        return $this->verificationService;
    }
}
