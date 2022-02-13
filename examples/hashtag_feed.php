<?php
require '../vendor/autoload.php';
use TikScraper\Api;

header('Content-Type: application/json');
$api = new Api();
$hashtag = $api->getHashtagFeed('funny');
echo $hashtag->ToJson(true);
