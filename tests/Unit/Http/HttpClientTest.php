<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Http;

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
use Calisero\Sms\Http\HttpClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class HttpClientTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    /** @var MockObject&RequestFactoryInterface */
    private $requestFactory;

    /** @var MockObject&StreamFactoryInterface */
    private $streamFactory;

    /** @var AuthProviderInterface&MockObject */
    private $authProvider;

    /** @var IdempotencyKeyProviderInterface&MockObject */
    private $idempotencyKeyProvider;
    private HttpClient $client;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->authProvider = $this->createMock(AuthProviderInterface::class);
        $this->idempotencyKeyProvider = $this->createMock(IdempotencyKeyProviderInterface::class);

        $this->client = new HttpClient(
            $this->httpClient,
            $this->requestFactory,
            $this->streamFactory,
            $this->authProvider,
            'https://rest.calisero.ro/v1',
            $this->idempotencyKeyProvider
        );
    }

    public function testGetRequest(): void
    {
        $path = '/messages';
        $queryParams = ['page' => 2];

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'https://rest.calisero.ro/v1/messages?page=2')
            ->willReturn($request);

        $this->authProvider
            ->expects($this->once())
            ->method('getToken')
            ->willReturn('test-bearer-token');

        $request->expects($this->exactly(3))
            ->method('withHeader')
            ->willReturnCallback(function ($name, $value) use ($request) {
                $this->assertContains($name, ['Authorization', 'User-Agent', 'Accept']);
                if ($name === 'Authorization') {
                    $this->assertSame('Bearer test-bearer-token', $value);
                } elseif ($name === 'User-Agent') {
                    $this->assertSame('Calisero-PHP/1.0.0', $value);
                } elseif ($name === 'Accept') {
                    $this->assertSame('application/json', $value);
                }

                return $request;
            });

        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn('{"data":{"id":"msg_123","body":"test"}}');

        $result = $this->client->get($path, $queryParams);

        $this->assertSame(['data' => ['id' => 'msg_123', 'body' => 'test']], $result);
    }

    public function testPostRequest(): void
    {
        $path = '/messages';
        $data = ['recipient' => '+40742123456', 'body' => 'Test message'];

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'https://rest.calisero.ro/v1/messages')
            ->willReturn($request);

        $this->authProvider
            ->expects($this->once())
            ->method('getToken')
            ->willReturn('test-bearer-token');

        $this->idempotencyKeyProvider
            ->expects($this->once())
            ->method('generate')
            ->willReturn('idempotency-key-123');

        $request->expects($this->exactly(5))
            ->method('withHeader')
            ->willReturnCallback(function ($name, $value) use ($request) {
                $this->assertContains($name, ['Authorization', 'User-Agent', 'Accept', 'Content-Type', 'Idempotency-Key']);
                if ($name === 'Authorization') {
                    $this->assertSame('Bearer test-bearer-token', $value);
                } elseif ($name === 'User-Agent') {
                    $this->assertSame('Calisero-PHP/1.0.0', $value);
                } elseif ($name === 'Accept') {
                    $this->assertSame('application/json', $value);
                } elseif ($name === 'Content-Type') {
                    $this->assertSame('application/json', $value);
                } elseif ($name === 'Idempotency-Key') {
                    $this->assertSame('idempotency-key-123', $value);
                }

                return $request;
            });

        $this->streamFactory
            ->expects($this->once())
            ->method('createStream')
            ->with('{"recipient":"+40742123456","body":"Test message"}')
            ->willReturn($bodyStream);

        $request->expects($this->once())
            ->method('withBody')
            ->with($bodyStream)
            ->willReturn($request);

        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(201);

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn('{"data":{"id":"msg_created","status":"pending"}}');

        $result = $this->client->post($path, $data, true);

        $this->assertSame(['data' => ['id' => 'msg_created', 'status' => 'pending']], $result);
    }

    public function testPutRequest(): void
    {
        $path = '/opt-outs/opt_123';
        $data = ['reason' => 'Updated reason'];

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('PUT', 'https://rest.calisero.ro/v1/opt-outs/opt_123')
            ->willReturn($request);

        $this->authProvider
            ->expects($this->once())
            ->method('getToken')
            ->willReturn('test-bearer-token');

        $request->expects($this->exactly(4))
            ->method('withHeader')
            ->willReturnCallback(function ($name, $value) use ($request) {
                $this->assertContains($name, ['Authorization', 'User-Agent', 'Accept', 'Content-Type']);
                if ($name === 'Authorization') {
                    $this->assertSame('Bearer test-bearer-token', $value);
                } elseif ($name === 'User-Agent') {
                    $this->assertSame('Calisero-PHP/1.0.0', $value);
                } elseif ($name === 'Accept') {
                    $this->assertSame('application/json', $value);
                } elseif ($name === 'Content-Type') {
                    $this->assertSame('application/json', $value);
                }

                return $request;
            });

        $this->streamFactory
            ->expects($this->once())
            ->method('createStream')
            ->with('{"reason":"Updated reason"}')
            ->willReturn($bodyStream);

        $request->expects($this->once())
            ->method('withBody')
            ->with($bodyStream)
            ->willReturn($request);

        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn('{"data":{"id":"opt_123","reason":"Updated reason"}}');

        $result = $this->client->put($path, $data);

        $this->assertSame(['data' => ['id' => 'opt_123', 'reason' => 'Updated reason']], $result);
    }

    public function testDeleteRequest(): void
    {
        $path = '/messages/msg_123';

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('DELETE', 'https://rest.calisero.ro/v1/messages/msg_123')
            ->willReturn($request);

        $this->authProvider
            ->expects($this->once())
            ->method('getToken')
            ->willReturn('test-bearer-token');

        $request->expects($this->exactly(3))
            ->method('withHeader')
            ->willReturnCallback(function ($name, $value) use ($request) {
                $this->assertContains($name, ['Authorization', 'User-Agent', 'Accept']);
                if ($name === 'Authorization') {
                    $this->assertSame('Bearer test-bearer-token', $value);
                } elseif ($name === 'User-Agent') {
                    $this->assertSame('Calisero-PHP/1.0.0', $value);
                } elseif ($name === 'Accept') {
                    $this->assertSame('application/json', $value);
                }

                return $request;
            });

        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(204);

        $this->client->delete($path);
    }

    public function testUnauthorizedExceptionOn401(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Authentication failed');

        $this->setupErrorResponse(401, ['message' => 'Authentication failed']);
        $this->client->get('/messages');
    }

    public function testForbiddenExceptionOn403(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Access denied');

        $this->setupErrorResponse(403, ['message' => 'Access denied']);
        $this->client->get('/messages');
    }

    public function testNotFoundExceptionOn404(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Resource not found');

        $this->setupErrorResponse(404, ['message' => 'Resource not found']);
        $this->client->get('/messages/nonexistent');
    }

    public function testValidationExceptionOn422(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');

        $errorData = [
            'message' => 'Validation failed',
            'errors' => [
                'recipient' => ['The recipient field is required.'],
                'body' => ['The body field is required.'],
            ],
        ];

        $this->setupErrorResponse(422, $errorData);
        $this->client->post('/messages', []);
    }

    public function testRateLimitedExceptionOn429(): void
    {
        $this->expectException(RateLimitedException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $this->setupErrorResponse(429, ['message' => 'Rate limit exceeded']);
        $this->client->post('/messages', ['recipient' => '+40742123456', 'body' => 'test']);
    }

    public function testServerExceptionOn500(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Internal server error');

        $this->setupErrorResponse(500, ['message' => 'Internal server error']);
        $this->client->get('/messages');
    }

    public function testApiExceptionOnOtherErrorCodes(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Unknown error');

        $this->setupErrorResponse(418, ['message' => 'Unknown error']);
        $this->client->get('/messages');
    }

    public function testTransportExceptionOnHttpException(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('HTTP request failed: Connection failed');

        $request = $this->createMock(RequestInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $this->authProvider->method('getToken')->willReturn('test-bearer-token');
        $request->method('withHeader')->willReturn($request);

        // Create a test exception that implements ClientExceptionInterface
        $clientException = new class('Connection failed') extends \RuntimeException implements ClientExceptionInterface {};

        $this->httpClient
            ->expects($this->once())
            ->method('sendRequest')
            ->willThrowException($clientException);

        $this->client->get('/messages');
    }

    public function testGetRequestWithoutQueryParams(): void
    {
        $path = '/accounts/acc_123';

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'https://rest.calisero.ro/v1/accounts/acc_123')
            ->willReturn($request);

        $this->authProvider->method('getToken')->willReturn('test-bearer-token');
        $request->method('withHeader')->willReturn($request);
        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn('{"data":{"id":"acc_123"}}');

        $result = $this->client->get($path);

        $this->assertSame(['data' => ['id' => 'acc_123']], $result);
    }

    public function testPostRequestWithoutIdempotency(): void
    {
        $path = '/opt-outs';
        $data = ['phone' => '+40742123456', 'reason' => 'Test opt-out'];

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $this->authProvider->method('getToken')->willReturn('test-bearer-token');

        $request->expects($this->exactly(4))
            ->method('withHeader')
            ->willReturnCallback(function ($name, $value) use ($request) {
                $this->assertContains($name, ['Authorization', 'User-Agent', 'Accept', 'Content-Type']);
                if ($name === 'Authorization') {
                    $this->assertSame('Bearer test-bearer-token', $value);
                } elseif ($name === 'User-Agent') {
                    $this->assertSame('Calisero-PHP/1.0.0', $value);
                } elseif ($name === 'Accept') {
                    $this->assertSame('application/json', $value);
                } elseif ($name === 'Content-Type') {
                    $this->assertSame('application/json', $value);
                }

                return $request;
            });

        $this->streamFactory->method('createStream')->willReturn($bodyStream);
        $request->method('withBody')->willReturn($request);
        $this->httpClient->method('sendRequest')->willReturn($response);
        $response->method('getStatusCode')->willReturn(201);
        $response->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn('{"data":{"id":"opt_123"}}');

        $result = $this->client->post($path, $data, false);

        $this->assertSame(['data' => ['id' => 'opt_123']], $result);
    }

    private function setupErrorResponse(int $statusCode, array $errorData): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $bodyStream = $this->createMock(StreamInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $this->authProvider->method('getToken')->willReturn('test-bearer-token');

        // Mock all possible withHeader calls to return the request
        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        // Mock stream factory for POST requests
        $this->streamFactory->method('createStream')->willReturn($bodyStream);

        $this->httpClient->method('sendRequest')->willReturn($response);

        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn(json_encode($errorData));

        $response->method('getHeader')->with('X-Request-ID')->willReturn(['req_123']);
        $response->method('getHeaderLine')
            ->willReturnCallback(function ($name) {
                if ($name === 'X-Request-ID') {
                    return 'req_123';
                }
                if ($name === 'Retry-After') {
                    return '60';
                }

                return '';
            });
    }
}
