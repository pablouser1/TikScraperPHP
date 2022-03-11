<?php
require __DIR__ . '/../../vendor/autoload.php';
header('Content-Type: application/json');
$api = new TikScraper\Legacy;
$hashtag = $api->getHashtagFeed('funny');
echo $hashtag->ToJson(true);
