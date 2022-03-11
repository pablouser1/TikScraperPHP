<?php
require __DIR__ . '/../../vendor/autoload.php';
header('Content-Type: application/json');
$api = new TikScraper\Legacy;
$trending = $api->getVideoByID("7062801547058515206");
echo $trending->ToJson(true);
