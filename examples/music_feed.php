<?php
require '../vendor/autoload.php';
use TikScraper\Api;

header('Content-Type: application/json');
$api = new Api();
$hashtag = $api->getMusicFeed('Epic-Music-863502-6873501791145691137');
echo $hashtag->ToJson(true);
