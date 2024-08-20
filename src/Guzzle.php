<?php
namespace TikScraper;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use TikScraper\Constants\UserAgents;

class Guzzle {
    private string $userAgent;

    const DEFAULT_HEADERS = [
        "Accept" => "*/*",
        "Accept-Language" => "en-US,en;q=0.5",
        "Accept-Encoding" => "gzip, deflate, br",
        "Referer" => "https://www.tiktok.com/explore"
    ];

    private Client $client;
    private FileCookieJar $jar;

    function __construct(array $config) {
        // Base config
        $cookieFile = $config['cookie_path'] ?? sys_get_temp_dir() . '/tiktok.json';

        $this->jar = new FileCookieJar($cookieFile, true);

        $this->userAgent = $config['user_agent'] ?? UserAgents::DEFAULT;
        $httpConfig = [
            'timeout' => 5.0,
            'cookies' => $this->jar,
            'allow_redirects' => true,
            'headers' => [
                'User-Agent' => $this->userAgent,
                ...self::DEFAULT_HEADERS
            ]
        ];

        // PROXY CONFIG
        if (isset($config['proxy'])) {
            $httpConfig['proxy'] = $config['proxy'];
        }

        $this->client = new Client($httpConfig);
    }


    public function getClient(): Client {
        return $this->client;
    }

    public function getJar(): FileCookieJar {
        return $this->jar;
    }

    public function getUserAgent(): string {
        return $this->userAgent;
    }

    public function setUserAgent(string $useragent): void {
        $this->userAgent = $useragent;
    }
}
