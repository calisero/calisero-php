<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * A link of a message body shortened by Calisero, with its click statistics.
 */
class ShortenedLink
{
    private string $id;
    private string $originalLink;
    private string $shortenedLink;
    private int $clickCount;
    private ?string $lastClick;
    private string $createdAt;

    public function __construct(
        string $id,
        string $originalLink,
        string $shortenedLink,
        int $clickCount,
        ?string $lastClick,
        string $createdAt
    ) {
        $this->id = $id;
        $this->originalLink = $originalLink;
        $this->shortenedLink = $shortenedLink;
        $this->clickCount = $clickCount;
        $this->lastClick = $lastClick;
        $this->createdAt = $createdAt;
    }

    /**
     * Create a ShortenedLink instance from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        \assert(\is_string($data['id']));
        \assert(\is_string($data['original_link']));
        \assert(\is_string($data['shortened_link']));
        \assert(\is_string($data['created_at']));

        return new self(
            $data['id'],
            $data['original_link'],
            $data['shortened_link'],
            isset($data['click_count']) && \is_int($data['click_count']) ? $data['click_count'] : 0,
            isset($data['last_click']) && \is_string($data['last_click']) ? $data['last_click'] : null,
            $data['created_at']
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * The URL as it was written in the message body.
     */
    public function getOriginalLink(): string
    {
        return $this->originalLink;
    }

    /**
     * The short URL that replaced it in the SMS sent.
     */
    public function getShortenedLink(): string
    {
        return $this->shortenedLink;
    }

    /**
     * How many times the short URL was opened.
     */
    public function getClickCount(): int
    {
        return $this->clickCount;
    }

    /**
     * When the short URL was last opened; null if it never was.
     */
    public function getLastClick(): ?string
    {
        return $this->lastClick;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
