<?php
require '../vendor/autoload.php';
use TikScraper\Api;

header('Content-Type: application/json');
$api = new Api();
$hashtag = $api->getHashtag('funny');
echo $hashtag->ToJson(true);
