# TikWrapperPHP
A Wrapper for the TikTok API made with PHP

## How to Use
```php
$api = new \TikScraper\Api([
    'signer_url' => 'URL_HERE', # Defaults to http://localhost:8080/signature
    'user_agent' => 'YOUR_CUSTOM_USER_AGENT_HERE',
    'proxy' => [
        'host' => 'EXAMPLE_HOST',
        'port' => 8080,
        'user' => 'EXAMPLE_USER',
        'password' => 'EXAMPLE_PASSWORD'
    ]
], $cacheEngine);

$hashtag_feed = $api->getHashtagFeed();
echo $hashtag_feed->ToJSON(true);
```

## Signatures
TikTok uses a signature system to validate some requests, this library gets those signatures using a third party signer.
I personally use [this one](https://github.com/carcabot/tiktok-signature) and I highly recommend it.

## TODO
* Search

## Credits
HUGE thanks to the following projects, this wouldn't be possible without their help

* [TikTok-API-PHP](https://github.com/ssovit/TikTok-API-PHP)
* [TikTok-Api](https://github.com/davidteather/TikTok-Api)
* [tiktok-signature](https://github.com/carcabot/tiktok-signature)
* [tiktok-scraper](https://github.com/drawrowfly/tiktok-scraper)
