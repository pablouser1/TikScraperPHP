<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: application/json');
$api = new \TikScraper\Api([], true);
$item = $api->video('iggers_fromafrica', 7100576199444925702);
$full = $item->feed()->getFull();
echo $full->toJson();
