<?php
namespace TikScraper\Models;

class Feed extends Base {
    public Meta $meta;
    public Info $info;
    public array $items = [];
    public bool $hasMore = false;
    public ?int $minCursor = 0;
    public string $maxCursor = '0';

    private function setData(array $items, bool $hasMore, ?int $minCursor, string $maxCursor) {
        $this->items = $items;
        $this->hasMore = $hasMore;
        $this->minCursor = $minCursor;
        $this->maxCursor = $maxCursor;
    }

    public function fromReq(Response $req, ?int $minCursor = 0, string $ttwid = '') {
        $this->meta = new Meta($req->http_success, $req->code, $req->data);
        if ($this->meta->success) {
            $data = $req->data;
            if ($ttwid) {
                $maxCursor = $ttwid;
            } else {
                $maxCursor = $data->cursor ?? null;
            }
            $this->setData($data->itemList, $data->hasMore, $minCursor, $maxCursor);
        }
    }

    public function setInfo(Info $info) {
        $this->info = $info;
    }

    public function fromCache(object $cache) {
        $this->meta = new Meta(true, 200, '');
        $this->setData($cache->items, $cache->hasMore, $cache->minCursor, $cache->maxCursor);
    }
}
