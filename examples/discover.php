<?php
require __DIR__."/../vendor/autoload.php";
header("Content-Type: application/json");
$api = new \TikScraper\Api();
$result = $api->getDiscover();
echo $result->ToJson(true);
