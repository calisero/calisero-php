<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Http;

use Calisero\Sms\Auth\BearerTokenAuthProvider;
use Calisero\Sms\Contracts\HttpClientInterface;
use Calisero\Sms\Exceptions\DailyLimitExceededException;
use Calisero\Sms\Exceptions\NotFoundException;
use Calisero\Sms\Exceptions\RateLimitedException;
use Calisero\Sms\Exceptions\ServerException;
use Calisero\Sms\Exceptions\TransportException;
use Calisero\Sms\Exceptions\UnauthorizedException;
use Calisero\Sms\Exceptions\ValidationException;
use Calisero\Sms\Http\ClientException;
use Calisero\Sms\Http\Factory\HttpFactory;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\Http\RequestInterface;
use Calisero\Sms\Http\Response;
use Calisero\Sms\Http\ResponseInterface;
use Calisero\Sms\SmsClient;
use PHPUnit\Framework\TestCase;

/**
 * HttpClient against real Request and Response objects, answered the way the API answers.
 */
class HttpClientResponseHandlingTest extends TestCase
{
    private const TRACE_ID = '9b80eef1-49d4-4502-85a8-febb68cc11a7';

    /** @var HttpClientInterface&object{requests: RequestInterface[]} */
    private $transport;

    public function testErrorMessageAndTraceIdComeFromTheErrorBody(): void
    {
        $client = $this->clientAnswering(
            new Response(404, [], '{"message":"Resource not found!","trace_id":"' . self::TRACE_ID . '"}')
        );

        try {
            $client->get('/messages/missing');
            $this->fail('Expected NotFoundException was not thrown');
        } catch (NotFoundException $e) {
            $this->assertSame('Resource not found!', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
            $this->assertSame(self::TRACE_ID, $e->getTraceId());
            $this->assertSame(self::TRACE_ID, $e->getRequestId());
            $this->assertSame(
                ['message' => 'Resource not found!', 'trace_id' => self::TRACE_ID],
                $e->getErrorDetails()
            );
        }
    }

    public function testTraceIdHeaderIsReadWhateverItsSpelling(): void
    {
        $client = $this->clientAnswering(
            new Response(401, ['x-trace-id' => [self::TRACE_ID]], '{"message":"Unauthenticated."}')
        );

        try {
            $client->get('/accounts/acc_1');
            $this->fail('Expected UnauthorizedException was not thrown');
        } catch (UnauthorizedException $e) {
            $this->assertSame('Unauthenticated.', $e->getMessage());
            $this->assertSame(self::TRACE_ID, $e->getTraceId());
            $this->assertSame(['message' => 'Unauthenticated.'], $e->getErrorDetails());
        }
    }

    public function testXRequestIdHeaderIsStillReadWithoutATraceId(): void
    {
        $client = $this->clientAnswering(new Response(404, ['X-Request-ID' => ['req_123']], ''));

        try {
            $client->get('/messages/missing');
            $this->fail('Expected NotFoundException was not thrown');
        } catch (NotFoundException $e) {
            $this->assertSame('req_123', $e->getTraceId());
        }
    }

    public function testErrorWithoutAJsonBodyGetsAGenericMessage(): void
    {
        $client = $this->clientAnswering(new Response(502, [], '<html>Bad Gateway</html>'));

        try {
            $client->get('/messages');
            $this->fail('Expected ServerException was not thrown');
        } catch (ServerException $e) {
            $this->assertSame('HTTP error 502', $e->getMessage());
            $this->assertSame(502, $e->getStatusCode());
            $this->assertNull($e->getTraceId());
            $this->assertSame([], $e->getErrorDetails());
        }
    }

    public function testValidationErrorsComeFromTheErrorsField(): void
    {
        $client = $this->clientAnswering(new Response(422, ['X-Trace-Id' => [self::TRACE_ID]], (string) \json_encode([
            'message' => 'The recipient field is required.',
            'errors' => ['recipient' => ['The recipient field is required.']],
            'trace_id' => self::TRACE_ID,
        ])));

        try {
            $client->post('/messages', ['body' => 'Hello'], true);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertSame('The recipient field is required.', $e->getMessage());
            $this->assertSame(self::TRACE_ID, $e->getTraceId());
            $this->assertSame(['recipient' => ['The recipient field is required.']], $e->getValidationErrors());
        }
    }

    public function testUnprocessableRequestWithoutFieldErrors(): void
    {
        // The API's own 422s carry field errors; a body without them must not pass for them.
        $client = $this->clientAnswering(new Response(422, ['X-Trace-Id' => [self::TRACE_ID]], (string) \json_encode([
            'message' => 'The request could not be processed.',
            'trace_id' => self::TRACE_ID,
        ])));

        try {
            $client->post('/messages', ['recipient' => '+40742***350', 'body' => 'Hello'], true);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertSame('The request could not be processed.', $e->getMessage());
            $this->assertSame([], $e->getValidationErrors());
            $this->assertSame(self::TRACE_ID, $e->getErrorDetails()['trace_id']);
        }
    }

    public function testRequestRateLimitRefusal(): void
    {
        // Lowercase names, as cURL reports them over HTTP/2.
        $client = $this->clientAnswering(new Response(429, [
            'retry-after' => ['17'],
            'x-ratelimit-limit' => ['240'],
            'x-ratelimit-remaining' => ['0'],
            'x-ratelimit-reset' => ['1790000000'],
            'x-trace-id' => [self::TRACE_ID],
        ], '{"message":"Too Many Attempts.","trace_id":"' . self::TRACE_ID . '"}'));

        try {
            $client->get('/messages');
            $this->fail('Expected RateLimitedException was not thrown');
        } catch (RateLimitedException $e) {
            $this->assertNotInstanceOf(DailyLimitExceededException::class, $e);
            $this->assertSame('Too Many Attempts.', $e->getMessage());
            $this->assertSame(429, $e->getStatusCode());
            $this->assertSame(self::TRACE_ID, $e->getTraceId());
            $this->assertSame(17, $e->getRetryAfter());
            $this->assertSame(240, $e->getRateLimitLimit());
            $this->assertSame(0, $e->getRateLimitRemaining());
            $this->assertSame(1790000000, $e->getRateLimitReset());
        }
    }

    public function testDailyLimitRefusal(): void
    {
        // Mixed-case names, as cURL reports them over HTTP/1.1.
        $client = $this->clientAnswering(new Response(429, [
            'Retry-After' => ['21600'],
            'X-Daily-Limit' => ['1000'],
            'X-Daily-Remaining' => ['0'],
            'X-RateLimit-Limit' => ['240'],
            'X-RateLimit-Remaining' => ['238'],
            'X-Trace-Id' => [self::TRACE_ID],
        ], (string) \json_encode([
            'message' => 'This account can send at most 1,000 messages a day. The limit resets at midnight, Romania time (2026-10-01T00:00:00+03:00). Contact us to raise it.',
            'code' => 'daily_limit_exceeded',
            'daily_limit' => 1000,
            'daily_remaining' => 0,
            'resets_at' => '2026-10-01T00:00:00+03:00',
            'trace_id' => self::TRACE_ID,
        ])));

        try {
            $client->post('/messages', ['recipient' => '+40742***350', 'body' => 'Hello'], true);
            $this->fail('Expected DailyLimitExceededException was not thrown');
        } catch (DailyLimitExceededException $e) {
            // Code written against RateLimitedException keeps catching it.
            $this->assertInstanceOf(RateLimitedException::class, $e);
            $this->assertStringStartsWith('This account can send at most 1,000 messages a day.', $e->getMessage());
            $this->assertSame(429, $e->getStatusCode());
            $this->assertSame(self::TRACE_ID, $e->getTraceId());
            $this->assertSame(1000, $e->getDailyLimit());
            $this->assertSame(0, $e->getDailyRemaining());
            $this->assertSame('2026-10-01T00:00:00+03:00', $e->getResetsAt());
            $this->assertSame(21600, $e->getRetryAfter());
            $this->assertSame(240, $e->getRateLimitLimit());
            $this->assertSame(238, $e->getRateLimitRemaining());
            $this->assertNull($e->getRateLimitReset());
            $this->assertSame('daily_limit_exceeded', $e->getErrorDetails()['code']);
        }
    }

    public function testDailyLimitFallsBackToTheHeadersForWhatTheBodyLacks(): void
    {
        $client = $this->clientAnswering(new Response(429, [
            'X-Daily-Limit' => ['500'],
            'X-Daily-Remaining' => ['0'],
        ], '{"message":"Daily limit reached.","code":"daily_limit_exceeded"}'));

        try {
            $client->post('/verifications', ['phone' => '+40742***350'], true);
            $this->fail('Expected DailyLimitExceededException was not thrown');
        } catch (DailyLimitExceededException $e) {
            $this->assertSame(500, $e->getDailyLimit());
            $this->assertSame(0, $e->getDailyRemaining());
            $this->assertNull($e->getResetsAt());
            $this->assertNull($e->getRetryAfter());
        }
    }

    public function testLastResponseKeepsTheAnswerOfEveryRequest(): void
    {
        $success = new Response(201, ['X-Trace-Id' => [self::TRACE_ID]], '{"data":{"id":"msg_1"}}');
        $failure = new Response(404, [], '{"message":"Resource not found!"}');
        $client = $this->clientAnswering($success, $failure);

        $this->assertNull($client->getLastResponse());

        $client->post('/messages', ['recipient' => '+40742***350', 'body' => 'Hello'], true);
        $this->assertSame($success, $client->getLastResponse());

        try {
            $client->get('/messages/missing');
            $this->fail('Expected NotFoundException was not thrown');
        } catch (NotFoundException $e) {
            $this->assertSame($failure, $client->getLastResponse());
        }
    }

    public function testLastResponseIsClearedWhenARequestGetsNoAnswer(): void
    {
        $client = $this->clientAnswering(
            new Response(200, [], '{"data":[]}'),
            new ClientException('Connection timed out')
        );

        $client->get('/messages');
        $this->assertNotNull($client->getLastResponse());

        try {
            $client->get('/messages');
            $this->fail('Expected TransportException was not thrown');
        } catch (TransportException $e) {
            $this->assertNull($client->getLastResponse());
        }
    }

    public function testUserAgentNamesTheLibraryPhpAndThePlatform(): void
    {
        $client = $this->clientAnswering(new Response(200, [], '{"data":[]}'));

        $client->get('/messages');

        // Like the Node.js library's: Calisero-SMS-Node/1.0.0 (Node.js v24.21.0; linux x64)
        $headers = $this->transport->requests[0]->getHeaders();
        $this->assertCount(1, $headers['User-Agent']);
        $this->assertMatchesRegularExpression(
            '/^Calisero-SMS-PHP\/' . \preg_quote(SmsClient::VERSION, '/')
            . ' \(PHP ' . \preg_quote(\PHP_VERSION, '/') . '; ' . \strtolower(\PHP_OS_FAMILY) . '( [a-z0-9_]+)?\)$/',
            $headers['User-Agent'][0]
        );
    }

    /**
     * A client whose requests get the given answers in turn: a response, or a
     * ClientException for a request that never got one.
     *
     * @param ClientException|ResponseInterface ...$answers
     */
    private function clientAnswering(...$answers): HttpClient
    {
        $this->transport = new class($answers) implements HttpClientInterface {
            /** @var RequestInterface[] */
            public array $requests = [];

            /** @var array<ClientException|ResponseInterface> */
            private array $answers;

            /**
             * @param array<ClientException|ResponseInterface> $answers
             */
            public function __construct(array $answers)
            {
                $this->answers = $answers;
            }

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->requests[] = $request;
                $answer = \array_shift($this->answers);

                if ($answer instanceof ClientException) {
                    throw $answer;
                }

                \assert($answer instanceof ResponseInterface);

                return $answer;
            }
        };

        return new HttpClient($this->transport, new HttpFactory(), new BearerTokenAuthProvider('test-token'));
    }
}
