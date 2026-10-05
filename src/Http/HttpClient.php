<?php

declare(strict_types=1);

namespace Calisero\Sms\Http;

use Calisero\Sms\Contracts\AuthProviderInterface;
use Calisero\Sms\Contracts\HttpClientInterface;
use Calisero\Sms\Contracts\IdempotencyKeyProviderInterface;
use Calisero\Sms\Contracts\RequestFactoryInterface;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\DailyLimitExceededException;
use Calisero\Sms\Exceptions\ForbiddenException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\Exceptions\RateLimitedException;
use Calisero\Sms\Exceptions\ServerException;
use Calisero\Sms\Exceptions\TransportException;
use Calisero\Sms\Exceptions\UnauthorizedException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\SmsClient;

/**
 * HTTP client wrapper that combines cURL client with authentication and error handling.
 */
class HttpClient
{
    private HttpClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private AuthProviderInterface $authProvider;
    private ?IdempotencyKeyProviderInterface $idempotencyKeyProvider;
    private string $baseUri;
    private ?ResponseInterface $lastResponse = null;

    public function __construct(
        HttpClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        AuthProviderInterface $authProvider,
        string $baseUri = 'https://rest.calisero.ro/api/v1',
        ?IdempotencyKeyProviderInterface $idempotencyKeyProvider = null
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
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
     *
     * @throws TransportException
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
            $request = $request->withBody($jsonData);
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
            $request = $request->withBody($jsonData);
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
     * The raw response to the last request, headers included: null before the
     * first request and after a request that got no answer.
     */
    public function getLastResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    /**
     * Build the full URI for a request.
     *
     * @param array<string, mixed> $queryParams
     */
    private function buildUri(string $path, array $queryParams = []): string
    {
        $uri = $this->baseUri . '/' . \ltrim($path, '/');

        if (!empty($queryParams)) {
            $query = \http_build_query($queryParams);
            $uri .= '?' . $query;
        }

        return $uri;
    }

    /**
     * Add authentication and standard headers to a request.
     */
    private function addHeaders(RequestInterface $request, bool $useIdempotency = false): RequestInterface
    {
        $request = $request->withHeader('Accept', 'application/json');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withHeader('User-Agent', 'Calisero-SMS-PHP/' . SmsClient::VERSION);

        // Add authentication
        $request = $request->withHeader('Authorization', 'Bearer ' . $this->authProvider->getToken());

        // Add idempotency key if requested and provider is available
        if ($useIdempotency && $this->idempotencyKeyProvider !== null) {
            $idempotencyKey = $this->idempotencyKeyProvider->generate();
            $request = $request->withHeader('Idempotency-Key', $idempotencyKey);
        }

        return $request;
    }

    /**
     * Send a request and handle the response.
     *
     * @return array<string, mixed>
     */
    private function sendRequest(RequestInterface $request): array
    {
        $this->lastResponse = null;

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new TransportException(
                'HTTP request failed: ' . $e->getMessage(),
                0,
                $e instanceof \Exception ? $e : null
            );
        }

        $this->lastResponse = $response;

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
        $body = $response->getBody();

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
                    $this->traceId($response, [])
                );
            }
        }

        // Handle error responses
        $errorData = $this->decodeErrorBody($body);
        $errorMessage = $this->errorMessage($errorData) ?? 'HTTP error ' . $statusCode;
        $traceId = $this->traceId($response, $errorData);

        // Throw appropriate exception based on status code
        switch ($statusCode) {
            case 400:
                throw new ApiException($errorMessage, 0, null, $statusCode, $traceId, $errorData);

            case 401:
                throw new UnauthorizedException($errorMessage, 0, null, $statusCode, $traceId, $errorData);

            case 403:
                throw new ForbiddenException($errorMessage, 0, null, $statusCode, $traceId, $errorData);

            case 404:
                throw new NotFoundException($errorMessage, 0, null, $statusCode, $traceId, $errorData);

            case 422:
                // Extract validation errors if they exist in a specific structure
                if (isset($errorData['errors']) && \is_array($errorData['errors'])) {
                    $validationErrors = $errorData['errors'];
                } elseif (isset($errorData['validation_errors']) && \is_array($errorData['validation_errors'])) {
                    $validationErrors = $errorData['validation_errors'];
                } else {
                    // A 422 without field errors: its message is the exception's, its
                    // body is in the error details
                    $validationErrors = [];
                }

                throw new ValidationException($errorMessage, 0, null, $statusCode, $traceId, $errorData, $validationErrors);

            case 429:
                throw $this->rateLimitException($response, $errorMessage, $traceId, $errorData);

            case 500:
            case 502:
            case 503:
            case 504:
                throw new ServerException($errorMessage, 0, null, $statusCode, $traceId, $errorData);

            default:
                throw new ApiException($errorMessage, 0, null, $statusCode, $traceId, $errorData);
        }
    }

    /**
     * The JSON body of an error response; empty when it has none.
     *
     * @return array<string, mixed>
     */
    private function decodeErrorBody(string $body): array
    {
        if ($body === '') {
            return [];
        }

        try {
            $decoded = \json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // Ignore JSON decode errors for error responses
            return [];
        }

        return \is_array($decoded) ? $decoded : [];
    }

    /**
     * The API sends the error's description as `message`; `error.message` is still
     * read, since earlier versions of this client looked for it there.
     *
     * @param array<string, mixed> $errorData
     */
    private function errorMessage(array $errorData): ?string
    {
        if (isset($errorData['message']) && \is_string($errorData['message']) && $errorData['message'] !== '') {
            return $errorData['message'];
        }

        $error = $errorData['error'] ?? null;

        if (\is_array($error) && isset($error['message']) && \is_string($error['message']) && $error['message'] !== '') {
            return $error['message'];
        }

        return null;
    }

    /**
     * The request's trace id: the X-Trace-Id header the API sends with every answer,
     * else the trace_id of the error body, else an X-Request-ID header.
     *
     * @param array<string, mixed> $errorData
     */
    private function traceId(ResponseInterface $response, array $errorData): ?string
    {
        $traceId = HeaderReader::string($response, 'X-Trace-Id');

        if ($traceId === null && isset($errorData['trace_id']) && \is_string($errorData['trace_id']) && $errorData['trace_id'] !== '') {
            $traceId = $errorData['trace_id'];
        }

        return $traceId ?? HeaderReader::string($response, 'X-Request-ID');
    }

    /**
     * A 429 comes from one of two limits, told apart by the body's code: the
     * account's daily sending limit (daily_limit_exceeded) or the request rate limit.
     *
     * @param array<string, mixed> $errorData
     */
    private function rateLimitException(
        ResponseInterface $response,
        string $errorMessage,
        ?string $traceId,
        array $errorData
    ): RateLimitedException {
        $retryAfter = HeaderReader::int($response, 'Retry-After');
        $rateLimitLimit = HeaderReader::int($response, 'X-RateLimit-Limit');
        $rateLimitRemaining = HeaderReader::int($response, 'X-RateLimit-Remaining');
        $rateLimitReset = HeaderReader::int($response, 'X-RateLimit-Reset');

        if (($errorData['code'] ?? null) !== DailyLimitExceededException::ERROR_CODE) {
            return new RateLimitedException(
                $errorMessage,
                0,
                null,
                429,
                $traceId,
                $errorData,
                $retryAfter,
                $rateLimitLimit,
                $rateLimitRemaining,
                $rateLimitReset
            );
        }

        return new DailyLimitExceededException(
            $errorMessage,
            0,
            null,
            429,
            $traceId,
            $errorData,
            $retryAfter,
            $rateLimitLimit,
            $rateLimitRemaining,
            $rateLimitReset,
            $this->intField($errorData, 'daily_limit') ?? HeaderReader::int($response, 'X-Daily-Limit'),
            $this->intField($errorData, 'daily_remaining') ?? HeaderReader::int($response, 'X-Daily-Remaining'),
            isset($errorData['resets_at']) && \is_string($errorData['resets_at']) ? $errorData['resets_at'] : null
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function intField(array $data, string $key): ?int
    {
        return isset($data[$key]) && \is_int($data[$key]) ? $data[$key] : null;
    }
}
