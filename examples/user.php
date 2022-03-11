<?php
require __DIR__ . '/../vendor/autoload.php';
header('Content-Type: application/json');
$api = new TikScraper\Api();
$user = $api->getUser('ibaillanos');
echo $user->ToJson(true);
