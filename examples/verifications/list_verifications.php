<?php

declare(strict_types=1);

use Calisero\Sms\SmsClient;

require_once __DIR__ . '/../../vendor/autoload.php';

$bearerToken = 'your-api-key-here';
$client = SmsClient::create($bearerToken);

$page = 1; // change as needed
$status = null; // or 'verified' / 'unverified'

$response = $client->verifications()->list($page, $status);

foreach ($response->getData() as $v) {
    echo sprintf("%s | %s | %s\n", $v->getId(), $v->getPhone(), $v->getStatus());
}
