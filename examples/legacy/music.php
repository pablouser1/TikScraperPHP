<?php
require '../vendor/autoload.php';
use TikScraper\Legacy;
header('Content-Type: application/json');
$api = new Legacy();
$hashtag = $api->getMusic('Casa-de-Papel-feat-Jul-6831786395300808706');
echo $hashtag->ToJson(true);
