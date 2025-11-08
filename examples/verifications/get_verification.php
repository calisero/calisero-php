<?php

declare(strict_types=1);

use Calisero\Sms\SmsClient;

require_once __DIR__ . '/../../vendor/autoload.php';

$bearerToken = 'your-api-key-here';
$client = SmsClient::create($bearerToken);

$verificationId = '019a62f1-66b7-7387-a64f-2742c12a2860';

$response = $client->verifications()->get($verificationId);
$verification = $response->getData();

echo "Verification {$verification->getId()} status: {$verification->getStatus()}\n";
