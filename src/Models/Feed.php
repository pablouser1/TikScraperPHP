<?php
namespace TikScraper\Models;
use TikScraper\Constants\Responses;

class Feed extends Base {
    public Meta $meta;
    public array $items = [];
    public bool $hasMore = false;
    public int $cursor = 0;

    /**
     * Build feed from TikTok response
     * @param \TikScraper\Models\Response $req TikTok response
     * @param mixed $cursor Cursor
     * @return \TikScraper\Models\Feed
     */
    public static function fromReq(Response $req, int $cursor = 0): self {
        $feed = new Feed;
        $feed->setMeta($req);
        if ($feed->meta->success) {
            $data = $req->jsonBody;

            // Videos
            if (isset($data->itemList)) {
                $feed->setItems($data->itemList);
            }

            // Comments
            if (isset($data->comments)) {
                $feed->setItems($data->comments);
            }

            // Nav
            $hasMore = false;
            if (isset($data->hasMore)) {
                $hasMore = $data->hasMore;
            }

            $cursor = 0;
            if (isset($data->cursor)) {
                $cursor = $data->cursor;
            }

            $feed->setNav($hasMore, $cursor);
        }

        return $feed;
    }

    public static function fromCache(object $cache): self {
        $feed = new Feed;
        $feed->setMeta(Responses::ok());
        $feed->setItems($cache->items);
        $feed->setNav($cache->hasMore, $cache->cursor);
        return $feed;
    }

    private function setMeta(Response $req) {
        $this->meta = new Meta($req);
    }

    private function setNav(bool $hasMore, int $cursor) {
        $this->hasMore = $hasMore;
        $this->cursor = $cursor;
    }

    private function setItems(array $items) {
        $this->items = $items;
    }
}
