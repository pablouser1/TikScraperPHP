<?php
namespace TikScraper;

class Download {
    protected $buffer_size = 256 * 1024;

    public function file_size($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, Common::USERAGENT);
        curl_setopt($ch, CURLOPT_REFERER, "https://www.tiktok.com/foryou");
        curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        return (int) $size;
    }

    private function watermark(string $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, Common::USERAGENT);
        curl_setopt($ch, CURLOPT_REFERER, "https://www.tiktok.com/");
        curl_setopt($ch, CURLOPT_BUFFERSIZE, $this->buffer_size);
        curl_exec($ch);
        curl_close($ch);
    }

    private function no_watermark(string $id) {
        $ch = curl_init('https://api2.musical.ly/aweme/v1/aweme/detail/?aweme_id=' . $id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, Common::USERAGENT);
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
    }

    public function url(string $item, $file_name = "tiktok-video", $watermark = true) {
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename="' . $file_name . '.mp4"');
        header("Content-Transfer-Encoding: Binary");
        if ($watermark) {
            $this->watermark($item);
        } else {
            $this->no_watermark($item);
        }
        exit;
    }
}
