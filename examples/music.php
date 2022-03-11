<?php
require __DIR__."/../vendor/autoload.php";

header('Content-Type: application/json');
$api = new TikScraper\Api();
$hashtag = $api->getMusic('Epic-Music-863502-6873501791145691137');
echo $hashtag->ToJson(true);
