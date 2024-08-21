<?php
use TikScraper\Constants\DownloadMethods;
use TikScraper\Download;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/common.php';

$api = buildApi();

$user = $api->user('willsmith');
$user->feed();

if ($user->ok()) {
    $downloader = new Download(DownloadMethods::DEFAULT);
    $downloader->url($user->getFeed()->items[0]->video->playAddr, 'tiktok-video', false);
}
