<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * The payload Calisero posts to a message's callback URL when its status changes:
 * sent, delivered or undelivered.
 *
 * It reaches your endpoint from outside your application, so it is validated
 * rather than asserted: a malformed payload throws \InvalidArgumentException.
 */
class DeliveryWebhookMessage
{
    private string $messageId;
    private ?string $sender;
    private string $recipient;
    private string $status;
    private ?string $scheduleAt;
    private ?string $sentAt;
    private ?string $deliveredAt;
    private float $price;
    private ?float $remainingBalance;
    private ?int $dailyLimit;
    private ?int $dailyRemaining;
    private ?int $sentToday;

    public function __construct(
        string $messageId,
        ?string $sender,
        string $recipient,
        string $status,
        ?string $scheduleAt,
        ?string $sentAt,
        ?string $deliveredAt,
        float $price,
        ?float $remainingBalance = null,
        ?int $dailyLimit = null,
        ?int $dailyRemaining = null,
        ?int $sentToday = null
    ) {
        $this->messageId = $messageId;
        $this->sender = $sender;
        $this->recipient = $recipient;
        $this->status = $status;
        $this->scheduleAt = $scheduleAt;
        $this->sentAt = $sentAt;
        $this->deliveredAt = $deliveredAt;
        $this->price = $price;
        $this->remainingBalance = $remainingBalance;
        $this->dailyLimit = $dailyLimit;
        $this->dailyRemaining = $dailyRemaining;
        $this->sentToday = $sentToday;
    }

    /**
     * Read the raw JSON body of a callback request, e.g. file_get_contents('php://input').
     *
     * @throws \InvalidArgumentException when the body is not a JSON object or a
     *                                   required field is missing or malformed
     */
    public static function fromJson(string $json): self
    {
        try {
            $data = \json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Invalid delivery webhook payload: ' . $e->getMessage(), 0, $e);
        }

        if (!\is_array($data)) {
            throw new \InvalidArgumentException('Invalid delivery webhook payload: expected a JSON object.');
        }

        return self::fromArray($data);
    }

    /**
     * Read the decoded JSON body of a callback request.
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException when a required field is missing or malformed
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['price']) || !\is_numeric($data['price'])) {
            throw new \InvalidArgumentException('Invalid delivery webhook payload: "price" must be a number.');
        }

        return new self(
            self::requiredString($data, 'messageId'),
            isset($data['sender']) && \is_string($data['sender']) ? $data['sender'] : null,
            self::requiredString($data, 'recipient'),
            self::requiredString($data, 'status'),
            isset($data['scheduleAt']) && \is_string($data['scheduleAt']) ? $data['scheduleAt'] : null,
            isset($data['sentAt']) && \is_string($data['sentAt']) ? $data['sentAt'] : null,
            isset($data['deliveredAt']) && \is_string($data['deliveredAt']) ? $data['deliveredAt'] : null,
            (float) $data['price'],
            isset($data['remainingBalance']) && \is_numeric($data['remainingBalance']) ? (float) $data['remainingBalance'] : null,
            isset($data['dailyLimit']) && \is_int($data['dailyLimit']) ? $data['dailyLimit'] : null,
            isset($data['dailyRemaining']) && \is_int($data['dailyRemaining']) ? $data['dailyRemaining'] : null,
            isset($data['sentToday']) && \is_int($data['sentToday']) ? $data['sentToday'] : null
        );
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * The sender ID the message went out under; null for the default sender.
     */
    public function getSender(): ?string
    {
        return $this->sender;
    }

    /**
     * The recipient in E.164 format.
     */
    public function getRecipient(): string
    {
        return $this->recipient;
    }

    /**
     * The delivery status: sent, delivered or undelivered.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * When the message was scheduled to go out, if it was scheduled.
     */
    public function getScheduleAt(): ?string
    {
        return $this->scheduleAt;
    }

    /**
     * When the message was handed to the network (statuses sent and delivered).
     */
    public function getSentAt(): ?string
    {
        return $this->sentAt;
    }

    /**
     * When the handset confirmed delivery (status delivered only).
     */
    public function getDeliveredAt(): ?string
    {
        return $this->deliveredAt;
    }

    /**
     * The price charged for the message's parts.
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * The account's balance after billing, if available.
     */
    public function getRemainingBalance(): ?float
    {
        return $this->remainingBalance;
    }

    /**
     * The account's daily sending limit when the callback was sent; null when no
     * daily limit applies.
     */
    public function getDailyLimit(): ?int
    {
        return $this->dailyLimit;
    }

    /**
     * How many messages the account could still send today when the callback was
     * sent; null when no daily limit applies.
     */
    public function getDailyRemaining(): ?int
    {
        return $this->dailyRemaining;
    }

    /**
     * The real messages the account created today, Romania time, when the callback
     * was sent: OTP codes included, test messages left out, one per message
     * whatever its parts. Null in payloads that predate the daily limit.
     */
    public function getSentToday(): ?int
    {
        return $this->sentToday;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function requiredString(array $data, string $field): string
    {
        if (!isset($data[$field]) || !\is_string($data[$field]) || $data[$field] === '') {
            throw new \InvalidArgumentException("Invalid delivery webhook payload: \"{$field}\" must be a non-empty string.");
        }

        return $data[$field];
    }
}
