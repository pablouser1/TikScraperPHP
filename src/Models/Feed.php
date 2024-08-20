<?php
namespace TikScraper\Models;
use TikScraper\Constants\Responses;

class Feed extends Base {
    public Meta $meta;
    public array $items = [];
    public bool $hasMore = false;
    public ?int $minCursor = 0;
    public string $maxCursor = '0';

    /**
     * Build feed from TikTok response
     * @param \TikScraper\Models\Response $req TikTok response
     * @param mixed $minCursor Cursor
     * @param string $ttwid ttwid token used for trending
     * @return \TikScraper\Models\Feed
     */
    public static function fromReq(Response $req, ?int $minCursor = 0, string $ttwid = ''): self {
        $feed = new Feed;
        $feed->setMeta($req);
        if ($feed->meta->success) {
            $data = $req->jsonBody;

            // Cursor
            $maxCursor = null;
            if ($ttwid) {
                $maxCursor = $ttwid;
            } else {
                if (isset($data->cursor)) {
                    $maxCursor = $data->cursor;
                }
            }

            // Items
            if (isset($data->itemList)) {
                $feed->setItems($data->itemList);
            }

            // Nav
            $hasMore = false;
            if (isset($data->hasMore)) {
                $hasMore = $data->hasMore;
            }

            if ($maxCursor) {
                $feed->setNav($hasMore, $minCursor, $maxCursor);
            }
        }

        return $feed;
    }

    public static function fromCache(object $cache): self {
        $feed = new Feed;
        $feed->setMeta(Responses::ok());
        $feed->setItems($cache->items);
        $feed->setNav($cache->hasMore, $cache->minCursor, $cache->maxCursor);
        return $feed;
    }

    private function setMeta(Response $req) {
        $this->meta = new Meta($req);
    }

    private function setNav(bool $hasMore, ?int $minCursor, string $maxCursor) {
        $this->hasMore = $hasMore;
        $this->minCursor = $minCursor;
        $this->maxCursor = $maxCursor;
    }

    private function setItems(array $items) {
        $this->items = $items;
    }
}
