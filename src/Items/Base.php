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

    function __construct(string $term, string $type, bool $legacy = false, Sender $sender, Cache $cache) {
        $this->term = $term;
        $this->type = $type;
        $this->legacy = $legacy;
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
        if (isset($this->info) && $this->info->meta->success) $this->cache->set($this->getCacheKey(), $this->info->ToJson());

        // Feed
        if (isset($this->feed) && $this->feed->meta->success) $this->cache->set($this->getCacheKey(true), $this->feed->ToJson());
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
        $feed_ok = true;

        if (isset($this->feed)) {
            $feed_ok = $this->feed->meta->success;
        }
        return $info_ok && $feed_ok;
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
        if ($this->cache->exists($key)) {
            $this->feed = $this->cache->handleFeed($key);
            return true;
        }
        return false;
    }

    /**
     * Make sure there is a valid info and there isn't a
     */
    protected function canSendFeed(): bool {
        return isset($this->info) && $this->info->meta->success;
    }
}
