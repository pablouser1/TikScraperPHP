# TikWrapperPHP
A Wrapper for the TikTok API made with PHP

## How to Use
### Standard mode (Recommended)
This mode requires signing, you can sign your requests using a [remote server](https://github.com/carcabot/tiktok-signature) or having a chromedriver running locally
```php
$api = new \TikScraper\Api([
    'user_agent' => 'YOUR_CUSTOM_USER_AGENT_HERE',
    'proxy' => [
        'host' => 'EXAMPLE_HOST',
        'port' => 8080,
        'user' => 'EXAMPLE_USER',
        'password' => 'EXAMPLE_PASSWORD'
    ],
    'signer' => [
        'remote_url' => 'http://localhost:8080/signature', // If you want to use remote signing
        'browser_url' => 'http://localhost:4444' // If you want to use local chromedriver
        'close_when_done' => true // --> Only for local signing <-- Set to true if you want to quit the browser after making the request (default true)
    ]
], $cacheEngine);

$hashtag_feed = $api->getHashtagFeed();
echo $hashtag_feed->ToJSON(true);
```

### Legacy mode
This mode is way faster to setup, it does not require any sort of signing but it may (or may not) be deleted in the future by TikTok. It also has some issues, like not being able to retrieve hashtag data past the first page
```php
$api = new \TikScraper\Legacy([
    'user_agent' => 'YOUR_CUSTOM_USER_AGENT_HERE',
    'proxy' => [
        'host' => 'EXAMPLE_HOST',
        'port' => 8080,
        'user' => 'EXAMPLE_USER',
        'password' => 'EXAMPLE_PASSWORD'
    ],
], $cacheEngine);

$hashtag_feed = $api->getHashtagFeed();
echo $hashtag_feed->ToJSON(true);
```

## TODO
* Search

## Credits
HUGE thanks to the following projects, this wouldn't be possible without their help

* [TikTok-API-PHP](https://github.com/ssovit/TikTok-API-PHP)
* [TikTok-Api](https://github.com/davidteather/TikTok-Api)
* [tiktok-signature](https://github.com/carcabot/tiktok-signature)
* [tiktok-scraper](https://github.com/drawrowfly/tiktok-scraper)
