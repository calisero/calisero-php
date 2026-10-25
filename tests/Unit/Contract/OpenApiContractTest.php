<?php

declare(strict_types=1);

namespace Calisero\Sms\Tests\Unit\Contract;

use Calisero\Sms\Auth\BearerTokenAuthProvider;
use Calisero\Sms\Contracts\HttpClientInterface;
use Calisero\Sms\Dto\CreateMessageRequest;
use Calisero\Sms\Dto\CreateOptOutRequest;
use Calisero\Sms\Dto\CreateVerificationRequest;
use Calisero\Sms\Dto\DeliveryWebhookMessage;
use Calisero\Sms\Dto\UpdateOptOutRequest;
use Calisero\Sms\Dto\VerificationCheckRequest;
use Calisero\Sms\Exceptions\DailyLimitExceededException;
use Calisero\Sms\Http\Factory\HttpFactory;
use Calisero\Sms\Http\HttpClient;
use Calisero\Sms\Http\RequestInterface;
use Calisero\Sms\Http\Response;
use Calisero\Sms\Http\ResponseInterface;
use Calisero\Sms\Services\AccountService;
use Calisero\Sms\Services\MessageService;
use Calisero\Sms\Services\OptOutService;
use Calisero\Sms\Services\VerificationService;
use PHPUnit\Framework\TestCase;

/**
 * Tests the library against the API's OpenAPI document (resources/openApi/api-v1.json):
 * every field the API takes can be sent, and every example answer of the document is read.
 *
 * When the API changes, replace the document: these tests then show what the library lacks.
 */
class OpenApiContractTest extends TestCase
{
    /** @var null|array<string, mixed> */
    private static ?array $document = null;

    /** @var HttpClientInterface&object{requests: RequestInterface[]} */
    private $transport;

    public function testTheDocumentIsTheOneOfTheSupportedApiVersion(): void
    {
        $this->assertStringStartsWith('1.0.15', self::string(self::path(self::document(), 'info', 'version')));
    }

    public function testEveryFieldOfAMessageIsSentAndTheAnswerIsRead(): void
    {
        $example = self::example('/messages', 'post', '201');
        $messages = new MessageService($this->clientAnswering(201, $example));

        $message = $messages->create(new CreateMessageRequest(
            '+40742123456',
            'Hello https://shop.example.test',
            'Hello',
            24,
            '2026-12-24 10:00:00',
            'https://hooks.example.test/sms',
            'CALISERO',
            true
        ))->getData();

        $this->assertSameKeys(self::schemaProperties('CreateMessageRequest'), $this->lastPayload());
        $this->assertSame(self::string(self::path($example, 'data', 'id')), $message->getId());
    }

    public function testTheDocumentedMessageIsRead(): void
    {
        $example = self::example('/messages/{messageId}', 'get', '200');
        $messages = new MessageService($this->clientAnswering(200, $example));

        $message = $messages->get('msg_1')->getData();

        $this->assertSame(self::string(self::path($example, 'data', 'id')), $message->getId());
        $this->assertSame(self::string(self::path($example, 'data', 'status')), $message->getStatus());
    }

    public function testTheDocumentedListOfMessagesIsRead(): void
    {
        $example = self::example('/messages', 'get', '200');
        $messages = new MessageService($this->clientAnswering(200, $example));

        $page = $messages->list();

        $this->assertCount(\count(self::array(self::path($example, 'data'))), $page->getData());
    }

    public function testEveryFieldOfAVerificationIsSentAndTheAnswerIsRead(): void
    {
        $example = self::example('/verifications', 'post', '201');
        $verifications = new VerificationService($this->clientAnswering(201, $example));

        $verification = $verifications->create(
            new CreateVerificationRequest('+40742123456', 'MyApp', 'Your code is {code}', 5)
        )->getData();

        $this->assertSameKeys(self::schemaProperties('CreateVerificationRequest'), $this->lastPayload());
        $this->assertSame(self::string(self::path($example, 'data', 'id')), $verification->getId());
    }

    public function testEveryFieldOfAVerificationCheckIsSentAndTheAnswerIsRead(): void
    {
        $example = self::example('/verifications/validate', 'put', '200');
        $verifications = new VerificationService($this->clientAnswering(200, $example));

        $verification = $verifications->validate(new VerificationCheckRequest('+40742123456', 'SBMH0f'))->getData();

        $this->assertSameKeys(self::schemaProperties('VerificationCheckRequest'), $this->lastPayload());
        $this->assertSame(self::string(self::path($example, 'data', 'status')), $verification->getStatus());
    }

    public function testTheDocumentedVerificationsAreRead(): void
    {
        $single = self::example('/verifications/{verificationId}', 'get', '200');
        $list = self::example('/verifications', 'get', '200');
        $verifications = new VerificationService($this->clientAnswering(200, $single, 200, $list));

        $verification = $verifications->get('ver_1')->getData();
        $page = $verifications->list();

        $this->assertSame(self::string(self::path($single, 'data', 'id')), $verification->getId());
        $this->assertCount(\count(self::array(self::path($list, 'data'))), $page->getData());
    }

    public function testEveryFieldOfAnOptOutIsSentAndTheAnswerIsRead(): void
    {
        $example = self::example('/opt-outs', 'post', '201');
        $optOuts = new OptOutService($this->clientAnswering(201, $example));

        $optOut = $optOuts->create(new CreateOptOutRequest('+40742123456', 'Customer request'))->getData();

        $this->assertSameKeys(self::schemaProperties('CreateOptOutRequest'), $this->lastPayload());
        $this->assertSame(self::string(self::path($example, 'data', 'id')), $optOut->getId());
    }

    public function testEveryFieldOfAnOptOutUpdateIsSentAndTheAnswerIsRead(): void
    {
        $example = self::example('/opt-outs/{optOutId}', 'put', '200');
        $optOuts = new OptOutService($this->clientAnswering(200, $example));

        $optOut = $optOuts->update('opt_1', new UpdateOptOutRequest('+40742123456', 'Customer asked to stop the reminders'))->getData();

        $this->assertSameKeys(self::schemaProperties('UpdateOptOutRequest'), $this->lastPayload());
        $this->assertSame(self::string(self::path($example, 'data', 'id')), $optOut->getId());
        $this->assertSame(self::string(self::path($example, 'data', 'reason')), $optOut->getReason());
    }

    public function testTheDocumentedOptOutsAreRead(): void
    {
        $single = self::example('/opt-outs/{optOutId}', 'get', '200');
        $list = self::example('/opt-outs', 'get', '200');
        $optOuts = new OptOutService($this->clientAnswering(200, $single, 200, $list));

        $optOut = $optOuts->get('opt_1')->getData();
        $page = $optOuts->list();

        $this->assertSame(self::string(self::path($single, 'data', 'id')), $optOut->getId());
        $this->assertCount(\count(self::array(self::path($list, 'data'))), $page->getData());
    }

    public function testTheDocumentedAccountIsRead(): void
    {
        $example = self::example('/accounts/{accountId}', 'get', '200');
        $accounts = new AccountService($this->clientAnswering(200, $example));

        $account = $accounts->get('acc_1')->getData();

        $this->assertSame(self::string(self::path($example, 'data', 'id')), $account->getId());
    }

    public function testTheDocumentedDeliveryWebhookIsRead(): void
    {
        $example = self::array(self::path(
            self::document(),
            'paths',
            '/messages',
            'post',
            'callbacks',
            'deliveryStatus',
            '{$request.body#/callback_url}',
            'post',
            'requestBody',
            'content',
            'application/json',
            'example'
        ));

        $webhook = DeliveryWebhookMessage::fromArray($example);

        $this->assertSame($example['messageId'], $webhook->getMessageId());
        $this->assertSame($example['status'], $webhook->getStatus());
        $this->assertSame($example['deliveredAt'], $webhook->getDeliveredAt());
        $this->assertSame($example['remainingBalance'], $webhook->getRemainingBalance());
        $this->assertSame($example['dailyLimit'], $webhook->getDailyLimit());
        $this->assertSame($example['dailyRemaining'], $webhook->getDailyRemaining());
        $this->assertSame($example['sentToday'], $webhook->getSentToday());
    }

    public function testEveryDocumentedWebhookStatusIsRead(): void
    {
        $statuses = self::array(self::path(
            self::document(),
            'components',
            'schemas',
            'DeliveryWebhookMessage',
            'properties',
            'status',
            'enum'
        ));

        $read = [];
        foreach ($statuses as $status) {
            $read[] = DeliveryWebhookMessage::fromArray([
                'messageId' => '019961db-14a7-7348-963f-5a7a789a969f',
                'recipient' => '+40742**350',
                'status' => $status,
                'price' => 0.0378,
            ])->getStatus();
        }

        $this->assertSame($statuses, $read);
    }

    public function testTheDocumentedDailyLimitRefusalOfAMessageIsReported(): void
    {
        $example = self::example('/messages', 'post', '429');
        $messages = new MessageService($this->clientAnswering(429, $example));

        try {
            $messages->create(new CreateMessageRequest('+40742123456', 'Hello'));
            $this->fail('The documented refusal was not reported.');
        } catch (DailyLimitExceededException $e) {
            $this->assertSame($example['message'], $e->getMessage());
            $this->assertSame($example['daily_limit'], $e->getDailyLimit());
            $this->assertSame($example['resets_at'], $e->getResetsAt());
            $this->assertSame($example['trace_id'], $e->getTraceId());
        }
    }

    public function testTheDocumentedDailyLimitRefusalOfAVerificationIsReported(): void
    {
        $example = self::example('/verifications', 'post', '429');
        $verifications = new VerificationService($this->clientAnswering(429, $example));

        try {
            $verifications->create(new CreateVerificationRequest('+40742123456', 'MyApp'));
            $this->fail('The documented refusal was not reported.');
        } catch (DailyLimitExceededException $e) {
            $this->assertSame($example['message'], $e->getMessage());
            $this->assertSame($example['resets_at'], $e->getResetsAt());
        }
    }

    /**
     * A client whose requests get the given answers in turn, each a status code
     * followed by the decoded body.
     *
     * @param array<string, mixed>|int ...$answers
     */
    private function clientAnswering(...$answers): HttpClient
    {
        $responses = [];
        for ($i = 0; $i < \count($answers); $i += 2) {
            $status = $answers[$i];
            \assert(\is_int($status));
            $responses[] = new Response($status, [], (string) \json_encode($answers[$i + 1]));
        }

        $this->transport = new class($responses) implements HttpClientInterface {
            /** @var RequestInterface[] */
            public array $requests = [];

            /** @var ResponseInterface[] */
            private array $responses;

            /**
             * @param ResponseInterface[] $responses
             */
            public function __construct(array $responses)
            {
                $this->responses = $responses;
            }

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->requests[] = $request;
                $response = \array_shift($this->responses);
                \assert($response instanceof ResponseInterface);

                return $response;
            }
        };

        return new HttpClient($this->transport, new HttpFactory(), new BearerTokenAuthProvider('test-token'));
    }

    /**
     * @return array<string, mixed>
     */
    private function lastPayload(): array
    {
        $request = \end($this->transport->requests);
        \assert($request instanceof RequestInterface);

        return self::array(\json_decode($request->getBody(), true));
    }

    /**
     * @param string[]             $expected
     * @param array<string, mixed> $payload
     */
    private function assertSameKeys(array $expected, array $payload): void
    {
        $actual = \array_keys($payload);
        \sort($expected);
        \sort($actual);

        $this->assertSame($expected, $actual);
    }

    /**
     * @return string[]
     */
    private static function schemaProperties(string $schema): array
    {
        return \array_map(
            'strval',
            \array_keys(self::array(self::path(self::document(), 'components', 'schemas', $schema, 'properties')))
        );
    }

    /**
     * The example answer the document gives for an operation and a status.
     *
     * @return array<string, mixed>
     */
    private static function example(string $path, string $method, string $status): array
    {
        return self::array(self::path(
            self::document(),
            'paths',
            $path,
            $method,
            'responses',
            $status,
            'content',
            'application/json',
            'example'
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private static function document(): array
    {
        if (self::$document === null) {
            $json = (string) \file_get_contents(\dirname(__DIR__, 3) . '/resources/openApi/api-v1.json');
            self::$document = self::array(\json_decode($json, true, 512, JSON_THROW_ON_ERROR));
        }

        return self::$document;
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return mixed
     */
    private static function path(array $data, string ...$keys)
    {
        foreach ($keys as $key) {
            if (!\is_array($data) || !\array_key_exists($key, $data)) {
                throw new \LogicException(\sprintf('The OpenAPI document has no "%s".', \implode(' > ', $keys)));
            }

            $data = $data[$key];
        }

        return $data;
    }

    /**
     * @param mixed $value
     *
     * @return array<string, mixed>
     */
    private static function array($value): array
    {
        \assert(\is_array($value));

        return $value;
    }

    /**
     * @param mixed $value
     */
    private static function string($value): string
    {
        \assert(\is_string($value));

        return $value;
    }
}
