<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/common.php';

$api = buildApi();
$item = $api->music('6715002916702259202');
$item->feed();

if ($item->ok()) {
    echo $item->getFull()->toJson(true);
} else {
    printError($item->error());
}
