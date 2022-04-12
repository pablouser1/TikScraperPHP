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

        foreach ($cookies_array as $key => $value) {
            $cookies .= "{$key}={$value};";
        }

        return $cookies;
    }

    static public function buildQuery(array $query = []): string {
        $query_merged = array_merge($query, [
            "aid" => 1988,
            "app_language" => 'en',
            "app_name" => "tiktok_web",
            "browser_language" => "en-us",
            "browser_name" => "Mozilla",
            "browser_online" => true,
            "browser_platform" => "iPhone",
            "browser_version" => urlencode(Common::DEFAULT_USERAGENT),
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
