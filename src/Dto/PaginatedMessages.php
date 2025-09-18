<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Paginated messages response DTO.
 */
class PaginatedMessages
{
    /** @var Message[] */
    private array $data;
    private PaginationLinks $links;
    private PaginationMeta $meta;

    /**
     * @param Message[] $data
     */
    public function __construct(array $data, PaginationLinks $links, PaginationMeta $meta)
    {
        $this->data = $data;
        $this->links = $links;
        $this->meta = $meta;
    }

    /**
     * Create a PaginatedMessages instance from API response.
     *
     * @param array<string, mixed> $response
     */
    public static function fromArray(array $response): self
    {
        \assert(\is_array($response['data']));
        \assert(\is_array($response['links']));
        \assert(\is_array($response['meta']));

        $messages = array_map(
            static function (mixed $messageData): Message {
                \assert(\is_array($messageData));

                return Message::fromArray($messageData);
            },
            $response['data']
        );

        return new self(
            $messages,
            PaginationLinks::fromArray($response['links']),
            PaginationMeta::fromArray($response['meta'])
        );
    }

    /**
     * @return Message[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getLinks(): PaginationLinks
    {
        return $this->links;
    }

    public function getMeta(): PaginationMeta
    {
        return $this->meta;
    }
}
