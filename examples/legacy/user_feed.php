<?php
require __DIR__ . '/../vendor/autoload.php';
use TikScraper\Legacy;
header('Content-Type: application/json');
$api = new Legacy();
$user = $api->getUserFeed('ibaillanos');
echo $user->ToJson(true);
