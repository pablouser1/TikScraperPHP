<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: application/json');
$api = new \TikScraper\Api([], true);
$item = $api->user('ibaillanos');
$full = $item->feed()->getFull();
echo $full->toJson();
