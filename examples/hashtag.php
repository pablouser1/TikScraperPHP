<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/common.php';

header('Content-Type: application/json');
$api = new \TikScraper\Api([
    'signer' => [
        'method' => 'remote',
        'url' => 'http://localhost:8080/signature'
    ]
]);
$item = $api->hashtag('funny');
$item->feed();

if ($item->ok()) {
    echo $item->getFull()->toJson(true);
} else {
    printError($item->error());
}
