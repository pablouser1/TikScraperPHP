<?php
require __DIR__ . '/common.php';
header('Content-Type: application/json');
$api = getStandardApi();
$trending = $api->getTrending();
echo $trending->ToJson(true);
