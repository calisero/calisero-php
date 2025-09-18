<?php

declare(strict_types=1);

namespace Calisero\Sms;

use Calisero\Sms\Auth\BearerTokenAuthProvider;
use Calisero\Sms\Contracts\AuthProviderInterface;
use Calisero\Sms\Contracts\HttpClientInterface;
use Calisero\Sms\Contracts\IdempotencyKeyProviderInterface;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\IdempotencyKey\UuidIdempotencyKeyProvider;
use Calisero\Sms\Services\AccountService;
use Calisero\Sms\Services\MessageService;
use Calisero\Sms\Services\OptOutService;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Main SMS API client.
 */
class SmsClient
{
    private HttpClient $httpClient;
    private MessageService $messageService;
    private OptOutService $optOutService;
    private AccountService $accountService;

    public function __construct(
        HttpClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        AuthProviderInterface $authProvider,
        string $baseUri = 'https://rest.calisero.ro/api/v1',
        ?IdempotencyKeyProviderInterface $idempotencyKeyProvider = null
    ) {
        if ($idempotencyKeyProvider === null) {
            $idempotencyKeyProvider = new UuidIdempotencyKeyProvider();
        }

        $this->httpClient = new HttpClient(
            $httpClient,
            $requestFactory,
            $streamFactory,
            $authProvider,
            $baseUri,
            $idempotencyKeyProvider
        );

        $this->messageService = new MessageService($this->httpClient);
        $this->optOutService = new OptOutService($this->httpClient);
        $this->accountService = new AccountService($this->httpClient);
    }

    /**
     * Create a client instance with a bearer token.
     */
    public static function create(
        string $bearerToken,
        HttpClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $baseUri = 'https://rest.calisero.ro/api/v1',
        ?IdempotencyKeyProviderInterface $idempotencyKeyProvider = null
    ): self {
        $authProvider = new BearerTokenAuthProvider($bearerToken);

        return new self(
            $httpClient,
            $requestFactory,
            $streamFactory,
            $authProvider,
            $baseUri,
            $idempotencyKeyProvider
        );
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
}
