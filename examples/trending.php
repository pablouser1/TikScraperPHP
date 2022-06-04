<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: application/json');
$api = new \TikScraper\Api([
    'signer' => [
        'remote_url' => 'http://localhost:8080/signature'
    ]
]);
$item = $api->trending();
$full = $item->feed()->getFeed();
echo $full->toJson();
