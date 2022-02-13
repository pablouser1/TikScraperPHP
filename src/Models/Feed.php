<?php
namespace TikScraper\Models;

class Feed extends Base {
    public Meta $meta;
    public Info $info;
    public array $items = [];
    public bool $hasMore = false;
    public ?int $minCursor = 0;
    public string $maxCursor = '0';

    public function setMeta(Response $req) {
        $this->meta = new Meta($req->http_success, $req->code, $req->data);
    }

    public function setInfo(Info $info) {
        $this->info = $info;
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
        $this->meta = new Meta($req->http_success, $req->code, $req->data);
        if ($this->meta->success) {
            $data = $req->data;
            if ($ttwid) {
                $maxCursor = $ttwid;
            } else {
                $maxCursor = $data->cursor ?? null;
            }
            $this->setItems($data->itemList);
            $this->setNav($data->hasMore, $minCursor, $maxCursor);
        }
    }

    public function fromCache(object $cache) {
        $this->meta = new Meta(true, 200, '');
        $info = new Info;
        $info->fromCache($cache->info);
        $this->setItems($cache->items);
        $this->setNav($cache->hasMore, $cache->minCursor, $cache->maxCursor);
        $this->setInfo($info);
    }
}
