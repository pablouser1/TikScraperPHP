# TikWrapperPHP
A Wrapper for the TikTok API made with PHP

## How to Use
```php
$api = new \TikScraper\Api([
    'user_agent' => 'YOUR_CUSTOM_USER_AGENT_HERE',
    'proxy' => [
        'host' => 'EXAMPLE_HOST',
        'port' => 8080,
        'user' => 'EXAMPLE_USER',
        'password' => 'EXAMPLE_PASSWORD'
    ],
    // More info about signing below
    'signer' => [
        'method' => 'remote',
        'url' => 'http://localhost:8080/signature',
        'close_when_done' => true // --> Only for browser signing <-- Set to true if you want to quit the browser after making the request (default true)
    ]
], $cacheEngine);

$hashtag = $api->hashtag('funny');
echo $hashtag->feed()->getFull()->toJson();
```

## Signing
For using TikScrapperPHP you need to use a signing service. There are multiple available:

### Remote signing
This method involves using an external signer.
* Set 'method' to `remote`
* Set 'url' to the signing endpoint

Currently supported:
* [tiktok-signature](https://github.com/carcabot/tiktok-signature) (uses headless Chrome browser)
* [SignTok](https://github.com/pablouser1/SignTok) (uses JSDOM)

### Browser
This method involves using a chromedriver instance.
* Set 'method' to `browser`
* Set 'url' to the chromedriver endpoint (usually http://localhost:4444)

You can also generate the documentation available using PHPDoc

## Caching
TikScrapperPHP supports caching requests, to use it you need to implement [CacheInterface.php](https://github.com/pablouser1/TikScraperPHP/blob/master/src/Interfaces/CacheInterface.php)

## TODO
* Search
* Comments
* X-Bogus support for ChromeDriver

## Credits
* @Sharqo78: Working TikTok downloader without watermark

HUGE thanks to the following projects, this wouldn't be possible without their help

* [TikTok-API-PHP](https://github.com/ssovit/TikTok-API-PHP)
* [TikTok-Api](https://github.com/davidteather/TikTok-Api)
* [tiktok-signature](https://github.com/carcabot/tiktok-signature)
* [tiktok-scraper](https://github.com/drawrowfly/tiktok-scraper)
