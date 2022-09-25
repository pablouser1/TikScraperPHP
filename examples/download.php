<?php

use TikScraper\Constants\DownloadMethods;
use TikScraper\Download;

require __DIR__ . '/../vendor/autoload.php';

$downloader = new Download(DownloadMethods::TTDOWN);

$downloader->url("https://www.tiktok.com/@willsmith/video/7079929224945093934", "example", false);
