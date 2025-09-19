<?php

declare(strict_types=1);

namespace Calisero\Sms\Contracts;

use Calisero\Sms\Http\RequestInterface;
use Calisero\Sms\Http\ResponseInterface;

/**
 * HTTP client interface for SMS API operations.
 */
interface HttpClientInterface
{
    /**
     * Send an HTTP request and return a response.
     */
    public function sendRequest(RequestInterface $request): ResponseInterface;
}
