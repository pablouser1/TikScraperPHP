# TikWrapperPHP
A Wrapper for the TikTok API made with PHP >= 8.0

## How to Use
```php
$api = new \TikScraper\Api([
    'debug' => false, // Debug mode
    'chromedriver' => 'http://localhost:4444', // Url to your chromedriver instance
    'verify_fp' => 'verify_...', // Cookie used for skipping captcha requests
    'user_agent' => 'YOUR_CUSTOM_USER_AGENT_HERE',
    'proxy' => 'http://user:password@hostname:port',
    'cookie_path' => '/your/custom/path/here/tiktok.json' // Path to store Guzzle's cookies, defaults to /tmp/tiktok.json
], $cacheEngine);

$tag = $api->hashtag('funny');
$tag->feed();

if ($hastag->ok()) {
    echo $hashtag->getFull()->toJson(true);
} else {
    print_r($hashtag->error());
}
```

## Caching
TikScrapperPHP supports caching requests, to use it you need to implement [ICache.php](https://github.com/pablouser1/TikScraperPHP/blob/master/src/Interfaces/ICache.php)

## Known issues
* The project `sapistudio/seleniumstealth` has been abandoned for 3 years now, TikTok's anti-bot systems may still get triggered

## TODO
* Search
* Comments
### Left to implement from legacy
* Custom UA for both Selenium and Guzzle
* Proxy for Selenium requests
* Fix Stream
* Re-add TikTok's HTML challenge (is this still happening???)
* Test if caching works properly
* Check if response from API wants a captcha
* For the love of god, actually document everything properly this time

## Credits
* @Sharqo78: Working TikTok downloader without watermark

HUGE thanks to the following projects, this wouldn't be possible without their help

* [TikTok-API-PHP](https://github.com/ssovit/TikTok-API-PHP)
* [TikTok-Api](https://github.com/davidteather/TikTok-Api)
* [tiktok-signature](https://github.com/carcabot/tiktok-signature)
* [tiktok-scraper](https://github.com/drawrowfly/tiktok-scraper)
