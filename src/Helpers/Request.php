<?php
namespace TikScraper\Helpers;

use TikScraper\Constants\UserAgents;

class Request {
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
        if (isset($proxy['host'], $proxy['port'])) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy['host'] . ":" . $proxy['port']);
            if (isset($proxy['username'], $proxy['password'])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['username'] . ":" . $proxy['password']);
            }
            curl_setopt($ch, CURLOPT_NOPROXY, '127.0.0.1,localhost');
        }
    }

    static public function getCookies(string $device_id, string $csrf_session_id): string {
        $cookies = '';
        $cookies_array = [
            'tt_webid' => $device_id,
            'tt_webid_v2' => $device_id,
            "csrf_session_id" => $csrf_session_id,
            "tt_csrf_token" => Algorithm::randomString(16)
        ];

        foreach ($cookies_array as $key => $value) {
            $cookies .= "{$key}={$value};";
        }

        return $cookies;
    }

    /**
     * Builds query for TikTok Api
     */
    static public function buildQuery(array $query = []): string {
        $query_merged = array_merge($query, [
            "aid" => 1988,
            "app_language" => 'en',
            "app_name" => "tiktok_web",
            "browser_language" => "en-us",
            "browser_name" => "Mozilla",
            "browser_online" => true,
            "browser_platform" => "iPhone",
            "browser_version" => urlencode(UserAgents::DEFAULT),
            "channel" => "tiktok_web",
            "cookie_enabled" => true,
            "device_platform" => "web_mobile",
            "focus_state" => true,
            "history_len" => rand(1, 5),
            "is_fullscreen" => false,
            "is_page_visible" => true,
            "os" => "ios",
            "priority_region" => "",
            "referer" => '',
            "region" => "us",
            "screen_width" => 1920,
            "screen_height" => 1080,
            "timezone_name" => "America/Chicago",
            "webcast_language" => "en"
        ]);
        return '?' . http_build_query($query_merged);
    }
}
