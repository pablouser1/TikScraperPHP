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
}
