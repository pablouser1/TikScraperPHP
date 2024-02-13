<?php
namespace TikScraper\Helpers;

class Request {
    /**
     * Builds query for TikTok Api
     */
    static public function buildQuery(array $query = [], string $msToken = ''): string {
        $query_merged = array_merge($query, [
            "WebIdLastTime" => time(),
            "aid" => 1988,
            "app_language" => 'en-US',
            "app_name" => "tiktok_web",
            "browser_language" => "en-US",
            "browser_name" => "Mozilla",
            "browser_online" => true,
            "browser_platform" => "Win32",
            "browser_version" => "5.0 (Windows)",
            "channel" => "tiktok_web",
            "cookie_enabled" => true,
            "current_region" => "US",
            "device_platform" => "web_pc",
            "enter_from" => "tiktok_web",
            "focus_state" => true,
            "history_len" => rand(1, 10),
            "is_fullscreen" => false,
            "is_non_personalized" => true,
            "is_page_visible" => true,
            "language" => "en",
            "os" => "windows",
            "priority_region" => "",
            "referer" => "",
            "region" => "US",
            "screen_width" => 1920,
            "screen_height" => 1080,
            "tz_name" => "America/Chicago",
            "webcast_language" => "en"
        ]);

        if ($msToken !== '') {
            $query_merged['msToken'] = $msToken;
        }

        return '?' . http_build_query($query_merged);
    }
}
