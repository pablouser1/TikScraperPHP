<?php
require '../vendor/autoload.php';
use TikScraper\Legacy;
header('Content-Type: application/json');
$api = new Legacy();
$hashtag = $api->getHashtagFeed('funny');
echo $hashtag->ToJson(true);
