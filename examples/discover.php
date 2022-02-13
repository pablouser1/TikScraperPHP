<?php
header("Content-Type: application/json");
include __DIR__."/../vendor/autoload.php";
$api = new \TikScraper\Api();
$result = $api->getDiscover();
echo $result->ToJson(true);
