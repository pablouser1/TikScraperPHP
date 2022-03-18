<?php
require __DIR__ . '/common.php';
header('Content-Type: application/json');
$api = getStandardApi();
$result = $api->getDiscover();
echo $result->ToJson(true);
