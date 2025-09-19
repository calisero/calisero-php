<?php

declare(strict_types=1);

namespace Calisero\Sms\Http\Factory;

use Calisero\Sms\Contracts\RequestFactoryInterface;
use Calisero\Sms\Http\Request;
use Calisero\Sms\Http\RequestInterface;

/**
 * HTTP factory for creating requests.
 */
class HttpFactory implements RequestFactoryInterface
{
    public function createRequest(string $method, string $uri): RequestInterface
    {
        return new Request($method, $uri);
    }
}
