<?php
namespace TikScraper\Interfaces;

interface DownloaderInterface {
    const BUFFER_SIZE = 256 * 1024;

    public function watermark(string $payload);
    public function noWatermark(string $payload);
}
