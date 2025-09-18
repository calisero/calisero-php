<?php

declare(strict_types=1);

namespace Calisero\Sms\Dto;

/**
 * Pagination meta DTO.
 */
class PaginationMeta
{
    private int $currentPage;
    private ?int $from;
    private string $path;
    private int $perPage;
    private ?int $to;

    public function __construct(int $currentPage, ?int $from, string $path, int $perPage, ?int $to)
    {
        $this->currentPage = $currentPage;
        $this->from = $from;
        $this->path = $path;
        $this->perPage = $perPage;
        $this->to = $to;
    }

    /**
     * Create a PaginationMeta instance from an array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        \assert(\is_int($data['current_page']));
        \assert(\is_string($data['path']));
        \assert(\is_int($data['per_page']));

        return new self(
            $data['current_page'],
            isset($data['from']) && \is_int($data['from']) ? $data['from'] : null,
            $data['path'],
            $data['per_page'],
            isset($data['to']) && \is_int($data['to']) ? $data['to'] : null
        );
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getFrom(): ?int
    {
        return $this->from;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getTo(): ?int
    {
        return $this->to;
    }
}
