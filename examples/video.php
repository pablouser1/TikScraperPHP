<?php
require __DIR__ . '/common.php';
header('Content-Type: application/json');
$api = getStandardApi();
$trending = $api->getVideoByID("7062801547058515206");
echo $trending->ToJson(true);
