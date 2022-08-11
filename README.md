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
    'signer' => [
        'remote_url' => 'http://localhost:8080/signature', // If you want to use remote signing
        'browser_url' => 'http://localhost:4444' // If you want to use local chromedriver
        'close_when_done' => true // --> Only for local signing <-- Set to true if you want to quit the browser after making the request (default true)
    ]
], $cacheEngine);

$hashtag = $api->hashtag();
echo $hashtag->feed()->getFull()->toJson();
```

For using this program you need to have a signing service, you can sign your requests using a [remote server](https://github.com/carcabot/tiktok-signature) or having a chromedriver running locally.

You can also generate the documentation available using PHPDoc

## TODO
* Search
* Discover does not work
* Add X-Bogus support

## Credits
HUGE thanks to the following projects, this wouldn't be possible without their help

* [TikTok-API-PHP](https://github.com/ssovit/TikTok-API-PHP)
* [TikTok-Api](https://github.com/davidteather/TikTok-Api)
* [tiktok-signature](https://github.com/carcabot/tiktok-signature)
* [tiktok-scraper](https://github.com/drawrowfly/tiktok-scraper)
