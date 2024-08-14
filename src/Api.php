<?php
namespace TikScraper;

use TikScraper\Interfaces\ICache;
use TikScraper\Items\User;
use TikScraper\Items\Hashtag;
use TikScraper\Items\Music;
use TikScraper\Items\Video;

class Api {
    private Sender $sender;
    private Cache $cache;

    function __construct(array $config = [], ?ICache $cache_engine = null) {
        $this->sender = new Sender($config);
        $this->cache = new Cache($cache_engine);
    }

    // -- Main methods -- //
    public function user(string $term): User {
        return new User($term, $this->sender, $this->cache);
    }

    public function hashtag(string $term): Hashtag {
        return new Hashtag($term, $this->sender, $this->cache);
    }

    public function music(string $term): Music {
        return new Music($term, $this->sender, $this->cache);
    }

    public function video(string $term): Video {
        return new Video($term, $this->sender, $this->cache);
    }
}
