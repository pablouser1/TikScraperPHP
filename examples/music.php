<?php
require '../vendor/autoload.php';
use TikScraper\Api;

header('Content-Type: application/json');
$api = new Api();
$hashtag = $api->getMusic('Casa-de-Papel-feat-Jul-6831786395300808706');
echo $hashtag->ToJson(true);
