<?php

declare(strict_types=1);

namespace Calisero\Sms\Http;

/**
 * HTTP client exception for request/response errors.
 */
class ClientException extends \Exception implements ClientExceptionInterface {}
