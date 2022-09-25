<?php
namespace TikScraper\Downloaders;

use TikScraper\Constants\UserAgents;
// use TikScraper\Helpers\Converter;
use TikScraper\Interfaces\DownloaderInterface;

class DefaultDownloader implements DownloaderInterface {
    public function watermark(string $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, UserAgents::DEFAULT);
        curl_setopt($ch, CURLOPT_REFERER, "https://www.tiktok.com/");
        curl_setopt($ch, CURLOPT_BUFFERSIZE, self::BUFFER_SIZE);
        curl_exec($ch);
        curl_close($ch);
    }

    public function noWatermark(string $url) {
        die("Default downloader without watermark currently does not work!");
        /*
        $id = Converter::urlToId($url);
        $ch = curl_init('https://api2.musical.ly/aweme/v1/aweme/detail/?aweme_id=' . $id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, UserAgents::DEFAULT);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        if (!curl_errno($ch)) {
            $json = json_decode($data);
            $nowatermark_id = $json->aweme_detail->video->download_addr->uri;
            curl_setopt($ch, CURLOPT_URL, 'https://api-h2.tiktokv.com/aweme/v1/play/?video_id=' . $nowatermark_id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, $this->buffer_size);
            curl_exec($ch);
            curl_close($ch);
        } else {
            die('Eror while fetching data!');
        }
        */
    }
}
