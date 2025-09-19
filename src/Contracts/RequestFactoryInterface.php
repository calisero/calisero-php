<?php

declare(strict_types=1);

namespace Calisero\Sms\Contracts;

use Calisero\Sms\Http\RequestInterface;

/**
 * Request factory interface for creating HTTP requests.
 */
interface RequestFactoryInterface
{
    public function createRequest(string $method, string $uri): RequestInterface;
}
