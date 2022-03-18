<?php
require __DIR__ . '/common.php';
header('Content-Type: application/json');
$api = getStandardApi();
$user = $api->getUser('ibaillanos');
echo $user->ToJson(true);
