<?php

declare(strict_types=1);

use Calisero\Sms\Dto\VerificationCheckRequest;
use Calisero\Sms\SmsClient;

require_once __DIR__ . '/../../vendor/autoload.php';

$bearerToken = 'your-api-key-here';
$client = SmsClient::create($bearerToken);

$request = new VerificationCheckRequest('+40742***350', 'ABC123');

$response = $client->verifications()->validate($request);
$verification = $response->getData();

echo "Phone {$verification->getPhone()} verified status: {$verification->getStatus()}\n";
