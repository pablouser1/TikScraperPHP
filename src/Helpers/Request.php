<?php
namespace TikScraper\Helpers;

class Request {
    /**
     * Builds query for TikTok Api
     * @param array $querry Custom query
     * @param string $verifyFp VerifyFP token
     * @param string $deviceid Device ID token
     * @todo investigate odinId origin
     * @return string Query as a string with a '?' prefix
     */
    static public function buildQuery(array $query, object $nav, string $verifyFp, string $device_id): string {
        $query_merged = array_merge($query, [
            "WebIdLastTime" => time(),
            "aid" => 1988,
            "app_language" => 'en-US',
            "app_name" => "tiktok_web",
            "browser_language" => $nav->browser_language,
            "browser_name" => $nav->browser_name,
            "browser_online" => "true",
            "browser_platform" => $nav->browser_platform,
            "browser_version" => $nav->browser_version,
            "channel" => "tiktok_web",
            "cookie_enabled" => "true",
            "data_collection_enabled" => "false",
            "device_id" => $device_id,
            "device_platform" => "web_pc",
            "focus_state" => "true",
            "history_len" => rand(1, 10),
            "is_fullscreen" => "true",
            "is_page_visible" => "true",
            "language" => "en",
            "os" => "windows",
            "priority_region" => "",
            "referer" => "",
            "region" => "US",
            "screen_width" => 1920,
            "screen_height" => 1080,
            "tz_name" => "America/Chicago",
            "user_is_login" => false,
            "webcast_language" => "en",
            "verifyFp" => $verifyFp
        ]);

        ksort($query_merged);

        return '?' . http_build_query($query_merged);
    }
}
