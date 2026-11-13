<?php

declare(strict_types=1);

namespace Calisero\Sms\Http;

use Calisero\Sms\Contracts\HttpClientInterface;

/**
 * cURL-based HTTP client for SMS API operations.
 *
 * Implements HttpClientInterface using PHP's cURL extension.
 */
class BaseHttpClient implements HttpClientInterface
{
    private int $timeout;
    private int $connectTimeout;

    public function __construct(int $timeout = 30, int $connectTimeout = 10)
    {
        if (!\extension_loaded('curl')) {
            throw new \RuntimeException('cURL extension is required');
        }

        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $curl = \curl_init();
        if (!$curl) {
            throw new ClientException('Failed to initialize cURL');
        }

        try {
            $options = [
                CURLOPT_URL => (string) $request->getUri(),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
                CURLOPT_CUSTOMREQUEST => $request->getMethod(),
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_MAXREDIRS => 0,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ];

            // Add headers
            $headers = [];
            foreach ($request->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    $headers[] = $name . ': ' . $value;
                }
            }
            if (!empty($headers)) {
                $options[CURLOPT_HTTPHEADER] = $headers;
            }

            // Add body for applicable methods
            $body = $request->getBody();
            if (!empty($body)) {
                $options[CURLOPT_POSTFIELDS] = $body;
            }

            \curl_setopt_array($curl, $options);

            // The API accepts TLS 1.2 and 1.3 only: never offer less. libcurl 7.54 and
            // later read this as "TLS 1.2 or later", older ones as "TLS 1.2".
            if (\defined('CURL_SSLVERSION_TLSv1_2') && !\curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2)) {
                throw new ClientException('cURL cannot use TLS 1.2, which the Calisero API requires');
            }

            $response = \curl_exec($curl);
            if ($response === false) {
                $error = \curl_error($curl);

                throw new ClientException('cURL error: ' . $error);
            }

            $headerSize = \curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $statusCode = \curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $headerData = \substr((string) $response, 0, (int) $headerSize);
            $body = \substr((string) $response, (int) $headerSize);

            return new Response($statusCode, $this->parseHeaders($headerData), $body);
        } finally {
            // Since PHP 8.0 the handle is freed with its last reference and curl_close()
            // does nothing; PHP 8.5 deprecates it.
            if (\PHP_VERSION_ID < 80000) {
                \curl_close($curl);
            }
        }
    }

    /**
     * Parse HTTP headers from response string.
     *
     * @return array<string, string[]>
     */
    private function parseHeaders(string $headerData): array
    {
        $headers = [];
        $lines = \explode("\r\n", \trim($headerData));

        foreach ($lines as $line) {
            if (\strpos($line, ':') !== false) {
                [$name, $value] = \explode(':', $line, 2);
                $name = \trim($name);
                $value = \trim($value);

                if (!isset($headers[$name])) {
                    $headers[$name] = [];
                }
                $headers[$name][] = $value;
            }
        }

        return $headers;
    }
}
