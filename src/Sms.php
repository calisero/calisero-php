<?php

declare(strict_types=1);

namespace Calisero\Sms;

use Calisero\Sms\Contracts\HttpClientInterface;
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Main entry point for the Calisero SMS API client.
 */
class Sms
{
    /**
     * Create a new SMS client with the specified configuration.
     *
     * @param string               $bearerToken The API bearer token
     * @param null|string          $baseUri     Optional base URI (defaults to API default)
     * @param array<string, mixed> $options     Additional options for the HTTP client
     */
    public static function client(
        string $bearerToken,
        ?string $baseUri = null,
        array $options = []
    ): SmsClient {
        $guzzleOptions = array_merge([
            'timeout' => 30,
            'connect_timeout' => 10,
        ], $options);

        $guzzleClient = new Client($guzzleOptions);

        $httpClient = new class($guzzleClient) implements HttpClientInterface {
            private Client $client;

            public function __construct(Client $client)
            {
                $this->client = $client;
            }

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                return $this->client->sendRequest($request);
            }
        };

        $requestFactory = new RequestFactory();
        $streamFactory = new StreamFactory();

        return SmsClient::create(
            $bearerToken,
            $httpClient,
            $requestFactory,
            $streamFactory,
            $baseUri ?? 'https://rest.calisero.ro/api/v1'
        );
    }

    /**
     * Create a new SMS client with custom HTTP client.
     *
     * @param string              $bearerToken The API bearer token
     * @param HttpClientInterface $httpClient  Custom HTTP client
     * @param null|string         $baseUri     Optional base URI
     */
    public static function clientWith(
        string $bearerToken,
        HttpClientInterface $httpClient,
        ?string $baseUri = null
    ): SmsClient {
        $requestFactory = new RequestFactory();
        $streamFactory = new StreamFactory();

        return SmsClient::create(
            $bearerToken,
            $httpClient,
            $requestFactory,
            $streamFactory,
            $baseUri ?? 'https://rest.calisero.ro/api/v1'
        );
    }
}
