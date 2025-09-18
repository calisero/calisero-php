<?php

declare(strict_types=1);

namespace Calisero\Sms\Http;

use Calisero\Sms\Contracts\AuthProviderInterface;
use Calisero\Sms\Contracts\HttpClientInterface;
use Calisero\Sms\Contracts\IdempotencyKeyProviderInterface;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\ForbiddenException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\Exceptions\RateLimitedException;
use Calisero\Sms\Exceptions\ServerException;
use Calisero\Sms\Exceptions\TransportException;
use Calisero\Sms\Exceptions\UnauthorizedException;
use Calisero\Sms\Exceptions\ValidationException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * HTTP client wrapper with authentication and error handling.
 */
class HttpClient
{
    private const API_VERSION = '1.0.0';
    private const USER_AGENT = 'Calisero-PHP/' . self::API_VERSION;

    private HttpClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private AuthProviderInterface $authProvider;
    private ?IdempotencyKeyProviderInterface $idempotencyKeyProvider;
    private string $baseUri;

    public function __construct(
        HttpClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        AuthProviderInterface $authProvider,
        string $baseUri = 'https://rest.calisero.ro/api/v1',
        ?IdempotencyKeyProviderInterface $idempotencyKeyProvider = null
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->authProvider = $authProvider;
        $this->baseUri = \rtrim($baseUri, '/');
        $this->idempotencyKeyProvider = $idempotencyKeyProvider;
    }

    /**
     * Send a GET request.
     *
     * @param array<string, mixed> $queryParams
     *
     * @return array<string, mixed>
     */
    public function get(string $path, array $queryParams = []): array
    {
        $uri = $this->buildUri($path, $queryParams);
        $request = $this->requestFactory->createRequest('GET', $uri);
        $request = $this->addHeaders($request);

        return $this->sendRequest($request);
    }

    /**
     * Send a POST request.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function post(string $path, array $data = [], bool $useIdempotency = false): array
    {
        $uri = $this->buildUri($path);
        $request = $this->requestFactory->createRequest('POST', $uri);
        $request = $this->addHeaders($request, $useIdempotency);

        if (!empty($data)) {
            $jsonData = \json_encode($data, JSON_THROW_ON_ERROR);
            $stream = $this->streamFactory->createStream($jsonData);

            /** @var RequestInterface $request */
            $request = $request->withBody($stream)
                ->withHeader('Content-Type', 'application/json');
        }

        return $this->sendRequest($request);
    }

    /**
     * Send a PUT request.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function put(string $path, array $data = []): array
    {
        $uri = $this->buildUri($path);
        $request = $this->requestFactory->createRequest('PUT', $uri);
        $request = $this->addHeaders($request);

        if (!empty($data)) {
            $jsonData = \json_encode($data, JSON_THROW_ON_ERROR);
            $stream = $this->streamFactory->createStream($jsonData);

            /** @var RequestInterface $request */
            $request = $request->withBody($stream)
                ->withHeader('Content-Type', 'application/json');
        }

        return $this->sendRequest($request);
    }

    /**
     * Send a DELETE request.
     *
     * @return array<string, mixed>
     */
    public function delete(string $path): array
    {
        $uri = $this->buildUri($path);
        $request = $this->requestFactory->createRequest('DELETE', $uri);
        $request = $this->addHeaders($request);

        return $this->sendRequest($request);
    }

    /**
     * Build URI with query parameters.
     *
     * @param array<string, mixed> $queryParams
     */
    private function buildUri(string $path, array $queryParams = []): string
    {
        $uri = $this->baseUri . '/' . \ltrim($path, '/');

        if (!empty($queryParams)) {
            $uri .= '?' . \http_build_query($queryParams);
        }

        return $uri;
    }

    private function addHeaders(RequestInterface $request, bool $useIdempotency = false): RequestInterface
    {
        /** @var RequestInterface $request */
        $request = $request->withHeader('Authorization', 'Bearer ' . $this->authProvider->getToken());

        /** @var RequestInterface $request */
        $request = $request->withHeader('User-Agent', self::USER_AGENT);

        /** @var RequestInterface $request */
        $request = $request->withHeader('Accept', 'application/json');

        if ($useIdempotency && $this->idempotencyKeyProvider !== null) {
            /** @var RequestInterface $request */
            $request = $request->withHeader('Idempotency-Key', $this->idempotencyKeyProvider->generate());
        }

        return $request;
    }

    /**
     * Send a PSR-7 request.
     *
     * @return array<string, mixed>
     */
    private function sendRequest(RequestInterface $request): array
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new TransportException(
                'HTTP request failed: ' . $e->getMessage(),
                0,
                $e instanceof \Exception ? $e : null
            );
        }

        return $this->handleResponse($response, $request);
    }

    /**
     * Handle HTTP response and convert to array.
     *
     * @return array<string, mixed>
     */
    private function handleResponse(ResponseInterface $response, RequestInterface $request): array
    {
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $requestId = $response->getHeaderLine('X-Request-ID') ?: null;

        // Handle successful responses
        if ($statusCode >= 200 && $statusCode < 300) {
            if (empty($body)) {
                return [];
            }

            try {
                $decoded = \json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                \assert(\is_array($decoded));

                return $decoded;
            } catch (\JsonException $e) {
                throw new ApiException(
                    'Failed to decode JSON response: ' . $e->getMessage(),
                    0,
                    $e,
                    $statusCode,
                    $requestId
                );
            }
        }

        // Handle error responses
        $errorData = null;
        $errorMessage = 'HTTP error ' . $statusCode;

        if (!empty($body)) {
            try {
                $decoded = \json_decode($body, true, 512, JSON_THROW_ON_ERROR);
                if (\is_array($decoded)) {
                    $errorData = $decoded;
                    $errorMessage = \is_string($errorData['message'] ?? null) ? $errorData['message'] : $errorMessage;
                }
            } catch (\JsonException $e) {
                // Ignore JSON decode errors for error responses
            }
        }

        switch ($statusCode) {
            case 401:
                throw new UnauthorizedException($errorMessage, 0, null, $statusCode, $requestId, $errorData ?? []);

            case 403:
                throw new ForbiddenException($errorMessage, 0, null, $statusCode, $requestId, $errorData ?? []);

            case 404:
                throw new NotFoundException($errorMessage, 0, null, $statusCode, $requestId, $errorData ?? []);

            case 422:
                $validationErrors = \is_array($errorData['errors'] ?? null) ? $errorData['errors'] : [];

                throw new ValidationException(
                    $errorMessage,
                    0,
                    null,
                    $statusCode,
                    $requestId,
                    $errorData ?? [],
                    $validationErrors
                );

            case 429:
                $retryAfter = $response->getHeaderLine('Retry-After');
                $retryAfterInt = $retryAfter ? (int) $retryAfter : null;

                throw new RateLimitedException(
                    $errorMessage,
                    0,
                    null,
                    $statusCode,
                    $requestId,
                    $errorData ?? [],
                    $retryAfterInt
                );

            case 500:
            case 502:
            case 503:
            case 504:
                throw new ServerException($errorMessage, 0, null, $statusCode, $requestId, $errorData ?? []);

            default:
                throw new ApiException($errorMessage, 0, null, $statusCode, $requestId, $errorData ?? []);
        }
    }
}
