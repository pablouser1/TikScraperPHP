<?php
namespace TikScraper\Models;
use TikScraper\Constants\Responses;

class Feed extends Base {
    public Meta $meta;
    public array $items = [];
    public bool $hasMore = false;
    public ?int $minCursor = 0;
    public string $maxCursor = '0';

    public function setMeta(Response $req) {
        $this->meta = new Meta($req);
    }

    public function setNav(bool $hasMore, ?int $minCursor, string $maxCursor) {
        $this->hasMore = $hasMore;
        $this->minCursor = $minCursor;
        $this->maxCursor = $maxCursor;
    }

    public function setItems(array $items) {
        $this->items = $items;
    }

    public function fromReq(Response $req, ?int $minCursor = 0, string $ttwid = '') {
        $this->meta = new Meta($req);
        if ($this->meta->success) {
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
                $this->setItems($data->itemList);
            }

            // Nav
            $hasMore = false;
            if (isset($data->hasMore)) {
                $hasMore = $data->hasMore;
            }

            if ($maxCursor) {
                $this->setNav($hasMore, $minCursor, $maxCursor);
            }
        }
    }

    public function fromCache(object $cache) {
        $this->meta = new Meta(Responses::ok());
        $this->setItems($cache->items);
        $this->setNav($cache->hasMore, $cache->minCursor, $cache->maxCursor);
    }
}
