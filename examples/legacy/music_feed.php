<?php
require __DIR__ . '/../../vendor/autoload.php';
header('Content-Type: application/json');
$api = new TikScraper\Legacy;
$hashtag = $api->getMusicFeed('Epic-Music-863502-6873501791145691137');
echo $hashtag->ToJson(true);
