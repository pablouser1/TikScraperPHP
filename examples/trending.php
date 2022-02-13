<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: application/json');
$api = new TikScraper\Api();
$trending = $api->getTrending();
echo $trending->ToJson(true);
