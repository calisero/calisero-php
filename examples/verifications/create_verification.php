<?php

declare(strict_types=1);

use Calisero\Sms\Dto\CreateVerificationRequest;
use Calisero\Sms\SmsClient;

require_once __DIR__ . '/../../vendor/autoload.php';

$bearerToken = 'your-api-key-here';
$client = SmsClient::create($bearerToken);

$request = new CreateVerificationRequest('+40742***350', 'Calisero');

$response = $client->verifications()->create($request);
$verification = $response->getData();

echo "Created verification ID: {$verification->getId()}\n";
echo "Status: {$verification->getStatus()}\n";
