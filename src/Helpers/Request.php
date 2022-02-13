<?php
namespace TikScraper\Helpers;

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
            "app_name" => "tiktok_web",
            "device_platform" => "web_mobile",
            "region" => "US",
            "priority_region" => "US",
            "os" => "ios",
            "referer" => '',
            "cookie_enabled" => true,
            "screen_width" => 1920,
            "screen_height" => 1080,
            "browser_language" => "en-US",
            "browser_platform" => "Linux",
            "browser_name" => "Mozilla",
            "browser_version" => "5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (Windows NT 10.0; Win64; x64) Chrome/90.0.4430.85 Safari/537.36",
            "browser_online" => true,
            "tz_name" => "America/Chicago",
            "is_page_visible" => true,
            "focus_state" => true,
            "is_fullscreen" => false,
            "history_len" => rand(0, 30),
            "language" => "en"
        ]);
        return '?' . http_build_query($query_merged);
    }
}
