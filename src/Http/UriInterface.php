<?php

declare(strict_types=1);

namespace Calisero\Sms\Http;

/**
 * URI interface for HTTP requests.
 */
interface UriInterface
{
    public function __toString(): string;

    public function getScheme(): string;

    public function getHost(): string;

    public function getPort(): ?int;

    public function getPath(): string;

    public function getQuery(): string;

    public function withScheme(string $scheme): self;

    public function withHost(string $host): self;

    public function withPort(?int $port): self;

    public function withPath(string $path): self;

    public function withQuery(string $query): self;
}
