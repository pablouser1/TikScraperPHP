<?php
require __DIR__ . '/common.php';
header('Content-Type: application/json');
$api = getStandardApi();
$hashtag = $api->getHashtagFeed('funny');
echo $hashtag->ToJson(true);
