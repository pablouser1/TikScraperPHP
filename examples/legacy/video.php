<?php
require __DIR__ . '/../vendor/autoload.php';
use TikScraper\Legacy;
header('Content-Type: application/json');
$api = new Legacy();
$trending = $api->getVideoByID("7062801547058515206");
echo $trending->ToJson(true);
