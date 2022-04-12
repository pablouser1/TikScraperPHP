<?php
namespace TikScraper;

use TikScraper\Common;

class Stream {
    private $buffer_size = 256 * 1024;

    public function url($url) {
        header("Content-Type: video/mp4");
        $ch = curl_init($url);

        $headers = [];
        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
        }

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_BUFFERSIZE => $this->buffer_size,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => Common::DEFAULT_USERAGENT,
            CURLOPT_REFERER => "https://www.tiktok.com/discover"
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
