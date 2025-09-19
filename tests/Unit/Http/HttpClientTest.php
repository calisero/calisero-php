<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Http;

use Calisero\Sms\Contracts\AuthProviderInterface;
use Calisero\Sms\Contracts\HttpClientInterface;
use Calisero\Sms\Contracts\IdempotencyKeyProviderInterface;
use Calisero\Sms\Contracts\RequestFactoryInterface;
use Calisero\Sms\Exceptions\ApiException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\Exceptions\UnauthorizedException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\Http\RequestInterface;
use Calisero\Sms\Http\ResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    /** @var HttpClientInterface&MockObject */
    private $httpClient;

    /** @var MockObject&RequestFactoryInterface */
    private $requestFactory;

    /** @var AuthProviderInterface&MockObject */
    private $authProvider;

    /** @var IdempotencyKeyProviderInterface&MockObject */
    private $idempotencyKeyProvider;

    /** @var HttpClient */
    private $client;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->authProvider = $this->createMock(AuthProviderInterface::class);
        $this->idempotencyKeyProvider = $this->createMock(IdempotencyKeyProviderInterface::class);

        $this->client = new HttpClient(
            $this->httpClient,
            $this->requestFactory,
            $this->authProvider,
            'https://rest.calisero.ro/v1',
            $this->idempotencyKeyProvider
        );
    }

    public function testGetRequest(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $responseData = ['data' => ['id' => 'test-id']];

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'https://rest.calisero.ro/v1/test')
            ->willReturn($request);

        $request->expects($this->exactly(4))
            ->method('withHeader')
            ->willReturnSelf();

        $this->authProvider
            ->expects($this->once())
            ->method('getToken')
            ->willReturn('test-token');

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
            ->willReturn(\json_encode($responseData));

        $response->expects($this->once())
            ->method('getHeaderLine')
            ->with('X-Request-ID')
            ->willReturn('');

        $result = $this->client->get('/test');

        $this->assertEquals($responseData, $result);
    }

    public function testPostRequest(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $requestData = ['key' => 'value'];
        $responseData = ['data' => ['id' => 'created-id']];

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'https://rest.calisero.ro/v1/test')
            ->willReturn($request);

        $request->expects($this->exactly(4))
            ->method('withHeader')
            ->willReturnSelf();

        $request->expects($this->once())
            ->method('withBody')
            ->with(\json_encode($requestData))
            ->willReturnSelf();

        $this->authProvider
            ->expects($this->once())
            ->method('getToken')
            ->willReturn('test-token');

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
            ->willReturn(\json_encode($responseData));

        $response->expects($this->once())
            ->method('getHeaderLine')
            ->with('X-Request-ID')
            ->willReturn('');

        $result = $this->client->post('/test', $requestData);

        $this->assertEquals($responseData, $result);
    }

    public function testValidationException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();
        $this->authProvider->method('getToken')->willReturn('test-token');
        $this->httpClient->method('sendRequest')->willReturn($response);

        $response->method('getStatusCode')->willReturn(422);
        $response->method('getBody')->willReturn('{"error":{"message":"Validation failed"},"errors":{"email":["Email is required"],"phone":["Phone format is invalid"]}}');
        $response->method('getHeaderLine')->willReturn('');

        try {
            $this->client->get('/test');
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertSame('Validation failed', $e->getMessage());
            $this->assertSame(422, $e->getStatusCode());

            $validationErrors = $e->getValidationErrors();
            $this->assertIsArray($validationErrors);
            $this->assertArrayHasKey('email', $validationErrors);
            $this->assertArrayHasKey('phone', $validationErrors);
            $this->assertSame(['Email is required'], $validationErrors['email']);
            $this->assertSame(['Phone format is invalid'], $validationErrors['phone']);
        }
    }

    public function testBadRequestException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();
        $this->authProvider->method('getToken')->willReturn('test-token');
        $this->httpClient->method('sendRequest')->willReturn($response);

        $response->method('getStatusCode')->willReturn(400);
        $response->method('getBody')->willReturn('{"error":{"message":"Bad request"}}');
        $response->method('getHeaderLine')->willReturn('');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Bad request');

        $this->client->get('/test');
    }

    public function testUnauthorizedException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();
        $this->authProvider->method('getToken')->willReturn('invalid-token');
        $this->httpClient->method('sendRequest')->willReturn($response);

        $response->method('getStatusCode')->willReturn(401);
        $response->method('getBody')->willReturn('{"error":{"message":"Unauthorized"}}');
        $response->method('getHeaderLine')->willReturn('');

        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Unauthorized');

        $this->client->get('/test');
    }

    public function testNotFoundException(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->requestFactory->method('createRequest')->willReturn($request);
        $request->method('withHeader')->willReturnSelf();
        $this->authProvider->method('getToken')->willReturn('test-token');
        $this->httpClient->method('sendRequest')->willReturn($response);

        $response->method('getStatusCode')->willReturn(404);
        $response->method('getBody')->willReturn('{"error":{"message":"Not found"}}');
        $response->method('getHeaderLine')->willReturn('');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not found');

        $this->client->get('/test');
    }
}
