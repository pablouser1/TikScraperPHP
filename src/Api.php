<?php
namespace TikScraper;

use TikScraper\Items\User;
use TikScraper\Items\Hashtag;
use TikScraper\Items\Music;
use TikScraper\Items\Video;
use TikScraper\Items\Trending;
use TikScraper\Models\Discover;
use TikScraper\Interfaces\CacheInterface;

class Api {
    private Sender $sender;
    private Cache $cache;

    function __construct(array $config = [], ?CacheInterface $cache_engine = null) {
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

    public function trending(): Trending {
        return new Trending($this->sender, $this->cache);
    }

    /**
     * Discover does not follow the same structure.
     * For some reason all /node endpoints are dead EXCEPT this one
     */
    public function discover(): Discover {
        $cacheKey = 'discover';
        if ($this->cache->exists($cacheKey)) return $this->cache->handleDiscover($cacheKey);
        $query = [
            'count' => 30,
            'from_page' => 'fyp',
            'noUser' => 0,
            'userId' => ''
        ];
        $req = $this->sender->sendApi('/node/share/discover', 'www', $query);
        $response = new Discover;
        $response->setMeta($req);
        if ($response->meta->success) {
            $response->setItems(
                $req->data->body[0]->exploreList,
                $req->data->body[1]->exploreList,
                $req->data->body[2]->exploreList
            );
            $this->cache->set($cacheKey, $response->toJson());
        }
        return $response;
    }
}
