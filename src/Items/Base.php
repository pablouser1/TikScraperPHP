<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Models\Feed;
use TikScraper\Models\Full;
use TikScraper\Models\Info;
use TikScraper\Models\Meta;
use TikScraper\Models\Response;
use TikScraper\Sender;

abstract class Base {
    protected string $term;
    private string $type;

    protected $cursor;

    protected Sender $sender;
    private Cache $cache;

    protected Info $info;
    protected Feed $feed;

    protected object $sigi;

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

    /**
     * Try to fetch feed from Cache or from Sigi State
     */
    protected function handleFeedPreload(string $key): bool {
        return $this->handleFeedCache() || $this->handleFeedZero($key);
    }

    protected function handleFeedCache(): bool {
        $key = $this->getCacheKey(true);
        $exists = $this->cache->exists($key);
        if ($exists) {
            $this->feed = $this->cache->handleFeed($key);
        }
        return $exists;
    }

    private function handleFeedZero(string $key): bool {
        // We must be on cursor 0 and have Sigi properly set
        if ($this->cursor === 0 && isset($this->sigi, $this->sigi->MobileItemModule, $this->sigi->MobileUserModule)) {
            $users = $this->sigi->MobileUserModule->users; // Get all users that made the posts

            $items = [];

            foreach ($this->sigi->MobileItemModule as $item) {
                $uniqueId = $item->author;
                $item->author = $users->{$uniqueId};
                $items[] = $item;
            }

            $nav = $this->sigi->MobileItemList->{$key}; // Get navigation state

            // Building Feed
            $realCursor = $nav->cursor === 0 ? count($items) : $nav->cursor; // Fixes bug that sets cursor to 0 even then there are multiple posts already
            $feed = new Feed;
            $feed->setMeta(new Response(200, "PLACEHOLDER"));
            $feed->setItems($items);
            $feed->setNav($nav->hasMore, 0, $realCursor);

            $this->feed = $feed;
            return true;
        }
        return false;
    }
}
