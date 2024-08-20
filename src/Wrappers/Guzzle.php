<?php
namespace TikScraper\Wrappers;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

/**
 * Wrapper for GuzzleHTTP
 */
class Guzzle {
    private string $userAgent;

    const DEFAULT_HEADERS = [
        "Accept" => "*/*",
        "Accept-Language" => "en-US,en;q=0.5",
        "Accept-Encoding" => "gzip, deflate, br",
        "Referer" => "https://www.tiktok.com/explore"
    ];

    private Client $client;

    function __construct(array $config, Selenium $selenium) {
        $driver = $selenium->getDriver();

        // Share cookies with Selenium
        $jar = new CookieJar();
        $cookies = $driver->manage()->getCookies();
        foreach ($cookies as $c) {
            $set = new SetCookie();
            $set->setName($c->getName());
            $set->setValue($c->getValue());
            $set->setDomain($c->getDomain());
            $jar->setCookie($set);
        }

        // Use selenium's user agent or user-defined
        $this->userAgent = $config['user_agent'] ?? $selenium->getUserAgent();
        $httpConfig = [
            'timeout' => 5.0,
            'cookies' => $jar,
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
}
