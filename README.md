# TikWrapperPHP
A Wrapper for the TikTok API made with PHP >= 8.1

## How to Use
```php
$api = new \TikScraper\Api([
    'debug' => false, // Debug mode
    'browser' => [
        'url' => 'http://localhost:4444', // Url to your chromedriver instance
        'close_when_done' => false, // Close chrome instance when request finishes
    ],
    'verify_fp' => 'verify_...', // Cookie used for skipping captcha requests
    'device_id' => '596845...' // Custom device id
    'user_agent' => 'YOUR_CUSTOM_USER_AGENT_HERE',
    'proxy' => 'http://user:password@hostname:port'
], $cacheEngine);

$tag = $api->hashtag('funny');
$tag->feed();

if ($hastag->ok()) {
    echo $hashtag->getFull()->toJson(true);
} else {
    print_r($hashtag->error());
}
```

## Documentation
An initial version of the documentation is available [here](https://pablouser1.github.io/TikScraperPHP/)

## Caching
TikScrapperPHP supports caching requests, to use it you need to implement [ICache.php](https://github.com/pablouser1/TikScraperPHP/blob/master/src/Interfaces/ICache.php)

## TODO
* Search
* Comments
### Left to implement from legacy
* For the love of god, actually document everything properly this time

## Credits
* @Sharqo78: Working TikTok downloader without watermark

HUGE thanks to the following projects, this wouldn't be possible without their help

* [puppeteer-extra-plugin-stealth](https://github.com/berstend/puppeteer-extra/blob/master/packages/puppeteer-extra-plugin-stealth), ported library to PHP
* [TikTok-API-PHP](https://github.com/ssovit/TikTok-API-PHP)
* [TikTok-Api](https://github.com/davidteather/TikTok-Api)
* [tiktok-signature](https://github.com/carcabot/tiktok-signature)
* [tiktok-scraper](https://github.com/drawrowfly/tiktok-scraper)
