<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: application/json');
$api = new TikScraper\Legacy();
$trending = $api->getHashtagFeed('femboy');
echo $trending->ToJson(true);
