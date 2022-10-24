<?php
namespace TikScraper\Downloaders;

use TikScraper\Constants\UserAgents;
use TikScraper\Helpers\Algorithm;
use TikScraper\Helpers\Converter;
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

    /**
     * Downloads TikTok without watermark using Android/iOS API
     * @link https://github.com/Sharqo78/VTik/blob/main/src/extractors/extractors.v
     */
    public function noWatermark(string $url) {
        $id = Converter::urlToId($url);

        $time = time();
        $query = [
            'aweme_id' => $id,
            'version_name' => '26.1.3',
            'version_code' => 2613,
            'build_number' => '26.1.3',
            'manifest_version_code' => 2613,
            'update_version_code' => 2613,
            'openudid' => Algorithm::randomString(8),
            'uuid' => Algorithm::randomString(8),
            '_rticket' => $time,
            'ts' => $time * 1000,
            'device_brand' => 'Google',
            'device_type' => 'Pixel%204',
            'device_platform' => 'android',
            'resolution' => '1080*1920',
            'dpi' => 420,
            'os_version' => 10,
            'os_api' => 29,
            'carrier_region' => 'US',
            'sys_region' => 'US',
            'region' => 'US',
            'app_name' => 'trill',
            'app_language' => 'en',
            'language' => 'en',
            'timezone_name' => 'America/New_York',
            'timezone_offset' => -14400,
            'channel' => 'googleplay',
            'ac' => 'wifi',
            'mcc_mnc' => 310260,
            'is_my_cn' => 0,
            'aid' => 1180,
            'ssmix' => 'a',
            'as' => 'a1qwert123',
            'cp' => 'cbfhckdckkde1'
        ];

        $ch = curl_init('https://api-h2.tiktokv.com/aweme/v1/feed/?' . http_build_query($query));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, UserAgents::DOWNLOAD);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        if (!curl_errno($ch)) {
            $json = json_decode($data);
            $nowatermark_url = $json->aweme_list[0]->video->play_addr->url_list[0];
            curl_setopt($ch, CURLOPT_URL, $nowatermark_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_BUFFERSIZE, self::BUFFER_SIZE);
            curl_exec($ch);
            curl_close($ch);
        } else {
            die('Eror while fetching data!');
        }
    }
}
