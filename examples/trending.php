<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: application/json');
$api = new \TikScraper\Api([], true);
$item = $api->trending();
$full = $item->feed()->getFeed();
echo $full->toJson();
