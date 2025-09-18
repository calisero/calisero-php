<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Pagination links DTO.
 */
class PaginationLinks
{
    private ?string $first;
    private ?string $last;
    private ?string $prev;
    private ?string $next;

    public function __construct(?string $first, ?string $last, ?string $prev, ?string $next)
    {
        $this->first = $first;
        $this->last = $last;
        $this->prev = $prev;
        $this->next = $next;
    }

    /**
     * Create a PaginationLinks instance from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['first']) && \is_string($data['first']) ? $data['first'] : null,
            isset($data['last']) && \is_string($data['last']) ? $data['last'] : null,
            isset($data['prev']) && \is_string($data['prev']) ? $data['prev'] : null,
            isset($data['next']) && \is_string($data['next']) ? $data['next'] : null
        );
    }

    public function getFirst(): ?string
    {
        return $this->first;
    }

    public function getLast(): ?string
    {
        return $this->last;
    }

    public function getPrev(): ?string
    {
        return $this->prev;
    }

    public function getNext(): ?string
    {
        return $this->next;
    }
}
