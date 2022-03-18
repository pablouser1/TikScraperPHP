<?php
require __DIR__ . '/common.php';
header('Content-Type: application/json');
$api = getStandardApi();
$user = $api->getUserFeed('ibaillanos');
echo $user->ToJson(true);
