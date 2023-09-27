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
$item = $api->trending();
$item->feed();

if ($item->feedOk()) {
    echo $item->getFeed()->toJson(true);
} else {
    printError($item->error());
}
