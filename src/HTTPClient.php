<?php
namespace TikScraper;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use TikScraper\Constants\UserAgents;

class HTTPClient {
    private string $userAgent;

    private const DEFAULT_HEADERS = [
        "referer" => "https://www.tiktok.com/discover"
    ];

    private Client $client;
    private FileCookieJar $jar;

    function __construct(array $config = []) {
        // Base config
        $cookieFile = $config['cookie_path'] ?? sys_get_temp_dir() . '/tiktok.json';
        $this->jar = new FileCookieJar($cookieFile);
        $this->userAgent = $config['user_agent'] ?? UserAgents::DEFAULT;
        $httpConfig = [
            'timeout' => 4.0,
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

    public function getClient() {
        return $this->client;
    }

    public function getJar() {
        return $this->jar;
    }

    public function getUserAgent() {
        return $this->userAgent;
    }

    public function setUserAgent(string $useragent) {
        $this->userAgent = $useragent;
    }
}