<?php

declare(strict_types=1);

namespace Calisero\Sms\Http;

/**
 * HTTP response implementation.
 */
class Response implements ResponseInterface
{
    private int $statusCode;

    /** @var array<string, string[]> */
    private array $headers;
    private string $body;

    /**
     * @param array<string, string[]> $headers
     */
    public function __construct(int $statusCode, array $headers, string $body)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string[]>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return string[]
     */
    public function getHeader(string $name): array
    {
        return $this->headers[\strtolower($name)] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        $values = $this->getHeader($name);

        return \implode(', ', $values);
    }
}
