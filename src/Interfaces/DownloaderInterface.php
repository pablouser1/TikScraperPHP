<?php
namespace TikScraper\Interfaces;

interface DownloaderInterface {
    public function watermark(string $payload);
    public function noWatermark(string $payload);
}
