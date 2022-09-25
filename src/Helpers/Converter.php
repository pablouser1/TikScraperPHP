<?php
namespace TikScraper\Helpers;

class Converter {
    /**
     * Gets a video's ID from a URL
     */
    static public function urlToId(string $url): string {
        $path = parse_url($url, PHP_URL_PATH);
        $path_arr = explode('/', $path);
        return $path_arr[count($path_arr) - 1];
    }
}
