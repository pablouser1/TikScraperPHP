<?php
require __DIR__."/../vendor/autoload.php";
header('Content-Type: application/json');
$api = new \TikScraper\Api();
$hashtag = $api->getHashtag('funny');
echo $hashtag->ToJson(true);
