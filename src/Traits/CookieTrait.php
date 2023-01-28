<?php
namespace TikScraper\Traits;

use TikScraper\Constants\UserAgents;

/**
 * Helps setting up cookies required for some parts of the program
 */
trait CookieTrait {
    protected string $cookieFile;

    protected function initCookies(): void {
        $this->cookieFile = sys_get_temp_dir() . '/tiktok.txt';

        // Send request to TikTok's homepage and store cookies if the cookie file doesn't exist
        if (!is_file($this->cookieFile)) {
            $this->__sendInitReq();
        }
    }

    private function __sendInitReq(): void {
        $ch = curl_init('https://www.tiktok.com/');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => UserAgents::DEFAULT,
            CURLOPT_ENCODING => 'utf-8',
            CURLOPT_AUTOREFERER => true,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile
        ]);

        curl_exec($ch);
        curl_close($ch);
    }
}
