<?php
namespace TikScraper\Downloaders;

use TikScraper\Interfaces\IDownloader;

/**
 * Video downloader using TikTok's internal API from WebApi
 */
class DefaultDownloader extends BaseDownloader implements IDownloader {
    public function __construct(array $config = []) {
        parent::__construct($config);
    }

    /**
     * Download video with watermark using downloadAddr
     * @param string $url Video URL
     * @return void
     */
    public function watermark(string $url): void {
        $client = $this->guzzle->getClient();

        $res = $client->get($url, [
            "stream" => true,
            "http_errors" => false
        ]);

        $body = $res->getBody();

        while (!$body->eof()) {
            echo $body->read(self::BUFFER_SIZE);
        }
    }

    /**
     * Downloads video without watermark using playAddr.
     * The method is identical from watermark, the url is the only thing that changes
     * @param string $url Video URL
     * @return void
     */
    public function noWatermark(string $url): void {
        $this->watermark($url);
    }
}
