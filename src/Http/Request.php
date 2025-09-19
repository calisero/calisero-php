<?php

declare(strict_types=1);

namespace Calisero\Sms\Http;

/**
 * HTTP request implementation.
 */
class Request implements RequestInterface
{
    private string $method;
    private UriInterface $uri;

    /** @var array<string, string[]> */
    private array $headers;
    private string $body;

    /**
     * @param array<string, string[]> $headers
     */
    public function __construct(string $method, string $uri, array $headers = [], string $body = '')
    {
        $this->method = $method;
        $this->uri = new Uri($uri);
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function withMethod(string $method): self
    {
        $clone = clone $this;
        $clone->method = $method;

        return $clone;
    }

    public function withUri(UriInterface $uri): self
    {
        $clone = clone $this;
        $clone->uri = $uri;

        return $clone;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = [$value];

        return $clone;
    }

    public function withBody(string $body): self
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }
}
