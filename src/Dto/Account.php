<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Account DTO.
 */
class Account
{
    private string $id;
    private string $code;
    private string $name;
    private ?string $description;
    private ?string $fiscalCode;
    private ?string $registryNumber;
    private ?string $iban;
    private string $city;
    private string $state;
    private string $country;
    private string $address;
    private ?int $postalCode;
    private ?string $email;
    private ?string $phone;
    private ?string $contactPerson;
    private float $credit;
    private string $status;
    private bool $sandbox;
    private ?int $dailyLimit;
    private ?int $dailyRemaining;
    private int $sentToday;
    private string $createdAt;

    public function __construct(
        string $id,
        string $code,
        string $name,
        ?string $description,
        ?string $fiscalCode,
        ?string $registryNumber,
        ?string $iban,
        string $city,
        string $state,
        string $country,
        string $address,
        ?int $postalCode,
        ?string $email,
        ?string $phone,
        ?string $contactPerson,
        float $credit,
        string $status,
        bool $sandbox,
        string $createdAt,
        ?int $dailyLimit = null,
        ?int $dailyRemaining = null,
        int $sentToday = 0
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->description = $description;
        $this->fiscalCode = $fiscalCode;
        $this->registryNumber = $registryNumber;
        $this->iban = $iban;
        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->email = $email;
        $this->phone = $phone;
        $this->contactPerson = $contactPerson;
        $this->credit = $credit;
        $this->status = $status;
        $this->sandbox = $sandbox;
        $this->createdAt = $createdAt;
        $this->dailyLimit = $dailyLimit;
        $this->dailyRemaining = $dailyRemaining;
        $this->sentToday = $sentToday;
    }

    /**
     * Create an Account instance from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        \assert(\is_string($data['id']));
        \assert(\is_string($data['code']));
        \assert(\is_string($data['name']));
        \assert(\is_string($data['city']));
        \assert(\is_string($data['state']));
        \assert(\is_string($data['country']));
        \assert(\is_string($data['address']));
        \assert(\is_numeric($data['credit']));
        \assert(\is_string($data['status']));
        \assert(\is_bool($data['sandbox']));
        \assert(\is_string($data['created_at']));

        return new self(
            $data['id'],
            $data['code'],
            $data['name'],
            isset($data['description']) && \is_string($data['description']) ? $data['description'] : null,
            isset($data['fiscal_code']) && \is_string($data['fiscal_code']) ? $data['fiscal_code'] : null,
            isset($data['registry_number']) && \is_string($data['registry_number']) ? $data['registry_number'] : null,
            isset($data['iban']) && \is_string($data['iban']) ? $data['iban'] : null,
            $data['city'],
            $data['state'],
            $data['country'],
            $data['address'],
            isset($data['postal_code']) && \is_int($data['postal_code']) ? $data['postal_code'] : null,
            isset($data['email']) && \is_string($data['email']) ? $data['email'] : null,
            isset($data['phone']) && \is_string($data['phone']) ? $data['phone'] : null,
            isset($data['contact_person']) && \is_string($data['contact_person']) ? $data['contact_person'] : null,
            (float) $data['credit'],
            $data['status'],
            $data['sandbox'],
            $data['created_at'],
            isset($data['daily_limit']) && \is_int($data['daily_limit']) ? $data['daily_limit'] : null,
            isset($data['daily_remaining']) && \is_int($data['daily_remaining']) ? $data['daily_remaining'] : null,
            isset($data['sent_today']) && \is_int($data['sent_today']) ? $data['sent_today'] : 0
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getFiscalCode(): ?string
    {
        return $this->fiscalCode;
    }

    public function getRegistryNumber(): ?string
    {
        return $this->registryNumber;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getContactPerson(): ?string
    {
        return $this->contactPerson;
    }

    public function getCredit(): float
    {
        return $this->credit;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * How many messages the account can send in a day, midnight to midnight,
     * Romania time; null when no daily limit applies.
     */
    public function getDailyLimit(): ?int
    {
        return $this->dailyLimit;
    }

    /**
     * How many messages the account can still send today; null when no daily
     * limit applies.
     */
    public function getDailyRemaining(): ?int
    {
        return $this->dailyRemaining;
    }

    /**
     * The real messages the account created today, Romania time: OTP codes
     * included, test messages left out, one per message whatever its parts.
     */
    public function getSentToday(): int
    {
        return $this->sentToday;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
