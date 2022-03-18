<?php
require __DIR__ . '/common.php';
header('Content-Type: application/json');
$api = getStandardApi();
$hashtag = $api->getMusic('Epic-Music-863502-6873501791145691137');
echo $hashtag->ToJson(true);
