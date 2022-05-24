<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: application/json');
$api = new \TikScraper\Api([], true);
$item = $api->music('Epic-Music-863502-6873501791145691137');
$full = $item->feed()->getFull();
echo $full->toJson();
