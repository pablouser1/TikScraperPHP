<?php
namespace TikScraper\Downloaders;

use TikScraper\Helpers\Algorithm;
use TikScraper\Helpers\Converter;
use TikScraper\Interfaces\DownloaderInterface;

/**
 * Video downloader using TikTok's own mobile API
 */
class DefaultDownloader extends BaseDownloader implements DownloaderInterface {
    public function __construct(array $config = []) {
        parent::__construct($config);
    }

    public function watermark(string $url) {
        $client = $this->httpClient->getClient();

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

        $client = $this->httpClient->getClient();

        $data = $client->get('https://api-h2.tiktokv.com/aweme/v1/feed/?' . http_build_query($query), [
            "http_errors" => false
        ]);

        if ($data->getStatusCode() === 200) {
            $json = json_decode($data->getBody());
            $nowatermark_url = $json->aweme_list[0]->video->play_addr->url_list[0];

            $res = $client->get($nowatermark_url, [
                "stream" => true,
                "http_errors" => false
            ]);

            $body = $res->getBody();

            while (!$body->eof()) {
                echo $body->read(self::BUFFER_SIZE);
            }
        } else {
            die("Error while fetching data");
        }
    }
}
