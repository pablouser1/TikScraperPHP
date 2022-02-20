<?php
namespace TikScraper;

use TikScraper\Models\Discover;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;

class Cache {
    const TIMEOUT = 3600;
    private bool $enabled = false;
    private $engine;

    function __construct($cache_engine) {
        if ($cache_engine) {
            $this->enabled = true;
            $this->engine = $cache_engine;
        }
    }

    private function get(string $key): object {
        if ($this->enabled) return $this->engine->get($key);
    }

    function exists(string $key): bool {
        if ($this->enabled) return $this->engine->exists($key);
        return false;
    }

    function set(string $key, string $data) {
        if ($this->enabled) $this->engine->set($key, $data, self::TIMEOUT);
    }

    function handleFeed(string $key): Feed {
        $feed = new Feed;
        $feed->fromCache($this->get($key));
        return $feed;
    }

    function handleInfo(string $key): Info {
        $info = new Info;
        $info->fromCache($this->get($key));
        return $info;
    }

    function handleDiscover(string $key): Discover {
        $discover = new Discover;
        $discover->fromCache($this->get($key));
        return $discover;
    }
}
