<?php

declare(strict_types=1);

namespace Calisero\Sms\Http;

/**
 * HTTP response interface.
 */
interface ResponseInterface
{
    public function getStatusCode(): int;

    /**
     * @return array<string, string[]>
     */
    public function getHeaders(): array;

    public function getBody(): string;

    /**
     * @return string[]
     */
    public function getHeader(string $name): array;

    public function getHeaderLine(string $name): string;
}
