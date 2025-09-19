<?php

declare(strict_types=1);

namespace Calisero\Sms\Http;

/**
 * HTTP request interface for SMS API operations.
 */
interface RequestInterface
{
    public function getMethod(): string;

    public function getUri(): UriInterface;

    /**
     * @return array<string, string[]>
     */
    public function getHeaders(): array;

    public function getBody(): string;

    public function withMethod(string $method): self;

    public function withUri(UriInterface $uri): self;

    public function withHeader(string $name, string $value): self;

    public function withBody(string $body): self;
}
