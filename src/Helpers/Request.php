<?php
namespace TikScraper\Helpers;

use TikScraper\Common;

class Request {
    static public function getCookies(string $device_id, string $csrf_session_id): string {
        $cookies = '';
        $cookies_array = [
            'tt_webid' => $device_id,
            'tt_webid_v2' => $device_id,
            "csrf_session_id" => $csrf_session_id,
            "tt_csrf_token" => Misc::generateRandomString(16)
        ];

        $i = 0;
        $cookies_index = count($cookies_array) - 1;

        foreach ($cookies_array as $key => $value) {
            if ($i === $cookies_index) $cookies .= "{$key}={$value}";
            else $cookies .= "{$key}={$value}; ";
            $i++;
        }
        return $cookies;
    }

    static public function buildQuery(array $query = []): string {
        $query_merged = array_merge($query, [
            "aid" => 1988,
            "app_name" => "tiktok_web",
            "device_platform" => "web_mobile",
            "region" => "us",
            "priority_region" => "",
            "os" => "ios",
            "referer" => '',
            "cookie_enabled" => true,
            "screen_width" => 1920,
            "screen_height" => 1080,
            "browser_language" => "en-us",
            "browser_platform" => "iPhone",
            "browser_name" => "Mozilla",
            "browser_version" => urlencode(Common::DEFAULT_USERAGENT),
            "browser_online" => true,
            "timezone_name" => "America/Chicago",
            "is_page_visible" => true,
            "focus_state" => true,
            "is_fullscreen" => false,
            "history_len" => rand(0, 30),
            "language" => "en",
            'msToken' => ''
        ]);
        return '?' . http_build_query($query_merged);
    }
}
