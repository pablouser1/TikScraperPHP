<?php

namespace TikScraper;

use TikScraper\Items\User;
use TikScraper\Items\Hashtag;
use TikScraper\Items\Music;
use TikScraper\Items\Video;
use TikScraper\Items\Trending;
use TikScraper\Models\Discover;

class Api {
    private Sender $sender;
    private Cache $cache;

    function __construct(array $config = [], $cache_engine = null) {
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
     * Discover is a (very) special case, does not follow the normal structure
     * By the way, for some reason all /node endpoints are dead EXCEPT this one
     */
    public function discover(): Discover {
        $cacheKey = 'discover';
        if ($this->cache->exists($cacheKey)) return $this->cache->handleDiscover($cacheKey);
        $query = [
            'userCount' => 30,
            'from_page' => 'discover'
        ];
        $req = $this->sender->sendApi('/node/share/discover', 'm', $query, '', false, '', false);
        $response = new Discover;
        $response->setMeta($req);
        if ($response->meta->success) {
            $response->setItems(
                $req->data->body[0]->exploreList,
                $req->data->body[1]->exploreList,
                $req->data->body[2]->exploreList
            );
            $this->cache->set($cacheKey, $response->ToJson());
        }
        return $response;
    }
}
