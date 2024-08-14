<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/common.php';

$api = buildApi();
$item = $api->user('ibaillanos');
$item->feed();

if ($item->ok()) {
    echo $item->getFull()->toJson(true);
} else {
    printError($item->error());
}
