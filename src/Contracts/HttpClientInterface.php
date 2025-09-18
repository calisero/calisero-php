<?php

declare(strict_types=1);

namespace Calisero\Sms\Contracts;

use Psr\Http\Client\ClientInterface;

/**
 * PSR-18 HTTP client interface extension for the SMS API.
 */
interface HttpClientInterface extends ClientInterface {}
