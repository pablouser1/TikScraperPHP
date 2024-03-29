<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Constants\Responses;
use TikScraper\Models\Feed;
use TikScraper\Models\Full;
use TikScraper\Models\Info;
use TikScraper\Models\Meta;
use TikScraper\Sender;

abstract class Base {
    protected string $term;
    private string $type;

    protected $cursor;

    protected Sender $sender;
    private Cache $cache;

    protected Info $info;
    protected Feed $feed;

    /** State used for getting item list from HTML */
    protected object $state;

    function __construct(string $term, string $type, Sender $sender, Cache $cache) {
        $this->term = urlencode($term);
        $this->type = $type;
        $this->sender = $sender;
        $this->cache = $cache;

        // Sets info from cache if it exists
        $key = $this->getCacheKey();
        if ($this->cache->exists($key)) {
            $this->info = $this->cache->handleInfo($key);
        }
    }

    /**
     * Destruct function, handles cache
     */
    function __destruct() {
        $key_info = $this->getCacheKey();
        $key_feed = $this->getCacheKey(true);

        // Info
        if ($this->infoOk() && !$this->cache->exists($key_info)) {
            $this->cache->set($key_info, $this->info->toJson());
        }

        // Feed
        if ($this->feedOk() && !$this->cache->exists($key_feed) && strpos($key_info, 'trending') === false) {
            $this->cache->set($key_feed, $this->feed->toJson());
        }
    }

    public function getInfo(): Info {
        return $this->info;
    }

    /**
     * Returns feed, returns null if $this->feed has not been called
     */
    public function getFeed(): ?Feed {
        return isset($this->feed) ? $this->feed : null;
    }

    public function getFull(): Full {
        return new Full($this->info, $this->feed);
    }

    /**
     * Checks if info request went OK
     */
    public function infoOk(): bool {
        return isset($this->info, $this->info->detail) && $this->info->meta->success;
    }

    /**
     * Checks if feed request went ok
     */
    public function feedOk(): bool {
        return isset($this->feed) && $this->feed->meta->success;
    }

    /**
     * Checks if both info and feed requests went ok
     */
    public function ok(): bool {
        return $this->infoOk() && $this->feedOk();
    }

    /**
     * Get Meta from feed if $this->feed has been called, info if not
     */
    public function error(): Meta {
        return isset($this->feed) ? $this->feed->meta : $this->info->meta;
    }

    /**
     * Builds cache key from type (video, tag...) and key (id of user, hashtag name...)
     * @param bool $addCursor Add current cursor to key
     */
    private function getCacheKey(bool $addCursor = false): string {
        $key = $this->type . '-' . $this->term;
        if ($addCursor) $key .= '-' . $this->cursor;
        return $key;
    }

    protected function handleFeedCache(): bool {
        $key = $this->getCacheKey(true);
        $exists = $this->cache->exists($key);
        if ($exists) {
            $this->feed = $this->cache->handleFeed($key);
        }
        return $exists;
    }
}
