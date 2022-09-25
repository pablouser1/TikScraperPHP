<?php
namespace TikScraper;

use TikScraper\Constants\DownloadMethods;
use TikScraper\Downloaders\DefaultDownloader;
use TikScraper\Downloaders\TtdownDownloader;
use TikScraper\Interfaces\DownloaderInterface;

class Download {
    private DownloaderInterface $downloader;

    function __construct(string $method = DownloadMethods::DEFAULT) {
        $this->downloader = $this->__getDownloader($method);
    }

    public function url(string $item, $file_name = "tiktok-video", $watermark = true) {
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename="' . $file_name . '.mp4"');
        header("Content-Transfer-Encoding: Binary");

        if ($watermark) {
            $this->downloader->watermark($item);
        } else {
            $this->downloader->noWatermark($item);
        }
        exit;
    }

    private function __getDownloader(string $method): DownloaderInterface {
        $class_str = '';
        switch ($method) {
            case DownloadMethods::DEFAULT:
                $class_str = DefaultDownloader::class;
                break;
            case DownloadMethods::TTDOWN:
                $class_str = TtdownDownloader::class;
                break;
            default:
                $class_str = DefaultDownloader::class;
        }

        return new $class_str();
    }
}
