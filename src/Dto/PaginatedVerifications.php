<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Paginated verifications response DTO.
 */
class PaginatedVerifications
{
    /** @var Verification[] */
    private array $data;
    private PaginationLinks $links;
    private PaginationMeta $meta;
    
    /**
     * @param Verification[] $data
     */
    public function __construct(array $data, PaginationLinks $links, PaginationMeta $meta)
    {
        $this->data  = $data;
        $this->links = $links;
        $this->meta  = $meta;
    }
    
    /**
     * @param array<string, mixed> $response
     */
    public static function fromArray(array $response): self
    {
        \assert(\is_array($response['data']));
        \assert(\is_array($response['links']));
        \assert(\is_array($response['meta']));
        
        $items = \array_map(
            static function ($item): Verification {
                \assert(\is_array($item));
                return Verification::fromArray($item);
            },
            $response['data']
        );
        
        return new self(
            $items,
            PaginationLinks::fromArray($response['links']),
            PaginationMeta::fromArray($response['meta'])
        );
    }
    
    /**
     * @return Verification[]
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
