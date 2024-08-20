<?php
namespace TikScraper;

use TikScraper\Interfaces\ICache;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;

/**
 * Wrapper around caching engines
 */
class Cache {
    const TIMEOUT = 3600;
    private bool $enabled = false;
    private ICache $engine;

    function __construct(?ICache $cache_engine = null) {
        if ($cache_engine) {
            $this->enabled = true;
            $this->engine = $cache_engine;
        }
    }

    /**
     * Get cached item or null if it doesn't exist
     * @param string $key Cache key
     * @return object|null Cache value
     */
    public function get(string $key): ?object {
        return $this->enabled ? $this->engine->get($key) : null;
    }

    /**
     * Checks if `key` exists
     * @param string $key Cache key
     * @return bool
     */
    public function exists(string $key): bool {
        return $this->enabled && $this->engine->exists($key);
    }

    /**
     * Writes data to cache
     * @param string $key Cache key
     * @param string $data Unserialized data
     * @return void
     */
    public function set(string $key, string $data): void {
        if ($this->enabled) $this->engine->set($key, $data, self::TIMEOUT);
    }

    /**
     * Gets feed from cache key
     * @param string $key Cache key
     * @return \TikScraper\Models\Feed Feed data
     */
    public function handleFeed(string $key): Feed {
        return Feed::fromCache($this->get($key));
    }

    /**
     * Gets info from cache key
     * @param string $key Cache key
     * @return \TikScraper\Models\Info Info data
     */
    public function handleInfo(string $key): Info {
        return Info::fromCache($this->get($key));
    }
}
