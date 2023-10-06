<?php
namespace TikScraper\Helpers;

use TikScraper\Constants\UserAgents;

class Request {
    /**
     * Builds query for TikTok Api
     */
    static public function buildQuery(array $query = [], string $msToken = ''): string {
        $query_merged = array_merge($query, [
            "WebIdLastTime" => time(),
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
            "language" => "en",
            "os" => "ios",
            "priority_region" => "",
            "referer" => '',
            "region" => "us",
            "screen_width" => 1920,
            "screen_height" => 1080,
            "tz_name" => "America/Chicago",
            "webcast_language" => "en",
            "msToken" => $msToken
        ]);
        return '?' . http_build_query($query_merged);
    }
}
