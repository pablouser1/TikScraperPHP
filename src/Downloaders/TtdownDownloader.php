<?php
namespace TikScraper\Downloaders;

use TikScraper\Constants\UserAgents;

class TtdownDownloader extends DefaultDownloader {
    public function noWatermark(string $url) {
        $ch = curl_init();
        $dom = new \DOMDocument();
        curl_setopt($ch, CURLOPT_URL, 'https://ttdownloader.com/en/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, UserAgents::DEFAULT);
        curl_setopt($ch, CURLOPT_COOKIEFILE, '');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        @$dom->loadHTML($data); // Ignore warnings
        $token_input = $dom->getElementById('token');
        $token = $token_input->getAttribute('value');
        if ($token) {
            curl_setopt($ch, CURLOPT_URL, 'https://ttdownloader.com/search/');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'url' => $url,
                'format' => '',
                'token' => $token
            ]));
            curl_setopt($ch, CURLOPT_REFERER, 'https://ttdownloader.com/en');
            $videos_html = curl_exec($ch);
            $dom->loadHTML($videos_html);
            $xpath = new \DomXPath($dom);
            $videos = $xpath->query("//a[contains(@class, 'download-link')]");
            if (count($videos)) {
                $no_watermark_url = $videos[0]->getAttribute('href');
                curl_setopt($ch, CURLOPT_URL, $no_watermark_url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, '');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
                curl_setopt($ch, CURLOPT_BUFFERSIZE, self::BUFFER_SIZE);
                curl_exec($ch);
                curl_close($ch);
            }
        }
    }
}
