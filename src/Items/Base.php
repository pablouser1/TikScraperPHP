<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Models\Feed;
use TikScraper\Models\Full;
use TikScraper\Models\Info;
use TikScraper\Models\Meta;
use TikScraper\Sender;

class Base {
    protected string $term;
    private string $type;
    protected bool $legacy;

    protected $cursor;

    protected Sender $sender;
    private Cache $cache;

    protected Info $info;
    protected Feed $feed;

    function __construct(string $term, string $type, Sender $sender, Cache $cache) {
        $this->term = urlencode($term);
        $this->type = $type;
        $this->sender = $sender;
        $this->cache = $cache;

        $key = $this->getCacheKey();
        if ($this->cache->exists($key)) $this->info = $this->cache->handleInfo($key);
    }

    /**
     * Destruct function, handles cache
     */
    function __destruct() {
        // Info
        $key_info = $this->getCacheKey();
        $key_feed = $this->getCacheKey(true);
        if (isset($this->info) && $this->info->meta->success && !$this->cache->exists($key_info)) $this->cache->set($key_info, $this->info->ToJson());

        // Feed
        if (isset($this->feed) && $this->feed->meta->success && !$this->cache->exists($key_feed) && strpos($key_info, 'trending') === false) $this->cache->set($key_feed, $this->feed->ToJson());
    }

    public function getInfo(): Info {
        return $this->info;
    }

    public function getFeed(): ?Feed {
        return isset($this->feed) ? $this->feed : null;
    }

    public function getFull(): Full {
        return new Full($this->info, $this->feed);
    }

    public function ok(): bool {
        $info_ok = $this->info->meta->success;

        if (isset($this->feed)) {
            $feed_ok = $this->feed->meta->success;
            return $info_ok && $feed_ok;
        }
        return $info_ok;
    }

    public function error(): Meta {
        return isset($this->feed) ? $this->feed->meta : $this->info->meta;
    }

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

    /**
     * Make sure there is valid info first (exists and it went ok)
     */
    protected function canSendFeed(): bool {
        return isset($this->info) && $this->info->meta->success;
    }
}
