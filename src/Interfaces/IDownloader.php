<?php
namespace TikScraper\Interfaces;

interface IDownloader {
    public function watermark(string $payload);
    public function noWatermark(string $payload);
}
