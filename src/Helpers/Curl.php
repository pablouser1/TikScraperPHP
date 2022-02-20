<?php
namespace TikScraper\Helpers;

class Curl {
    static public function extractCookies(string $data): array {
        $cookies = [];
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $data, $matches);
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        return $cookies;
    }

    static public function handleProxy(&$ch, array $proxy) {
        // Proxy
        if (isset($proxy['host'], $proxy['port'])) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy['host'] . ":" . $proxy['port']);
            if (isset($proxy['username'], $proxy['password'])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['username'] . ":" . $proxy['password']);
            }
            curl_setopt($ch, CURLOPT_NOPROXY, '127.0.0.1,localhost');
        }
    }
}
