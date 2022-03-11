<?php
require __DIR__ . '/../vendor/autoload.php';
use TikScraper\Legacy;
header('Content-Type: application/json');
$api = new Legacy();
$trending = $api->getTrending();
echo $trending->ToJson(true);
