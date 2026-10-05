<?php

declare(strict_types=1);

namespace Calisero\Sms\Http;

/**
 * Reads single-valued headers off a response.
 *
 * @internal
 */
class HeaderReader
{
    /**
     * The header's first value, trimmed; null when the header is absent or empty.
     */
    public static function string(ResponseInterface $response, string $name): ?string
    {
        $values = $response->getHeader($name);
        $value = isset($values[0]) ? \trim($values[0]) : '';

        return $value !== '' ? $value : null;
    }

    /**
     * The header's value as a non-negative integer; null when it is absent or not one.
     */
    public static function int(ResponseInterface $response, string $name): ?int
    {
        $value = self::string($response, $name);

        return $value !== null && \preg_match('/^\d+$/', $value) === 1 ? (int) $value : null;
    }
}
