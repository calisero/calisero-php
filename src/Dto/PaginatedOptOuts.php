<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Paginated opt-outs response DTO.
 */
class PaginatedOptOuts
{
    /** @var OptOut[] */
    private array $data;
    private PaginationLinks $links;
    private PaginationMeta $meta;

    /**
     * @param OptOut[] $data
     */
    public function __construct(array $data, PaginationLinks $links, PaginationMeta $meta)
    {
        $this->data = $data;
        $this->links = $links;
        $this->meta = $meta;
    }

    /**
     * Create a PaginatedOptOuts instance from API response.
     *
     * @param array<string, mixed> $response
     */
    public static function fromArray(array $response): self
    {
        \assert(\is_array($response['data']));
        \assert(\is_array($response['links']));
        \assert(\is_array($response['meta']));

        $optOuts = array_map(
            static function (mixed $optOutData): OptOut {
                \assert(\is_array($optOutData));

                return OptOut::fromArray($optOutData);
            },
            $response['data']
        );

        return new self(
            $optOuts,
            PaginationLinks::fromArray($response['links']),
            PaginationMeta::fromArray($response['meta'])
        );
    }

    /**
     * @return OptOut[]
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
