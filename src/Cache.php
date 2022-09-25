<?php
namespace TikScraper;

use TikScraper\Models\Discover;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Interfaces\CacheInterface;

class Cache {
    const TIMEOUT = 3600;
    private bool $enabled = false;
    private CacheInterface $engine;

    function __construct(?CacheInterface $cache_engine = null) {
        if ($cache_engine) {
            $this->enabled = true;
            $this->engine = $cache_engine;
        }
    }

    public function get(string $key): ?object {
        return $this->enabled ? $this->engine->get($key) : null;
    }

    public function exists(string $key): bool {
        return $this->enabled && $this->engine->exists($key);
    }

    public function set(string $key, string $data) {
        if ($this->enabled) $this->engine->set($key, $data, self::TIMEOUT);
    }

    public function handleFeed(string $key): Feed {
        $feed = new Feed;
        $feed->fromCache($this->get($key));
        return $feed;
    }

    public function handleInfo(string $key): Info {
        $info = new Info;
        $info->fromCache($this->get($key));
        return $info;
    }

    public function handleDiscover(string $key): Discover {
        $discover = new Discover;
        $discover->fromCache($this->get($key));
        return $discover;
    }
}
