<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: application/json');

$api = new TikScraper\Api([
    'proxy' => [
        'host' => '45.181.226.137',
        'port' => '999'
    ]
]);
$user = $api->getUserFeed('ibaillanos');
echo $user->ToJson(true);
