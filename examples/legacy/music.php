<?php
require __DIR__ . '/../../vendor/autoload.php';
header('Content-Type: application/json');
$api = new TikScraper\Legacy;
$hashtag = $api->getMusic('Casa-de-Papel-feat-Jul-6831786395300808706');
echo $hashtag->ToJson(true);
