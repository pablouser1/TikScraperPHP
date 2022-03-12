<?php
namespace TikScraper\Models;

class Discover extends Base {
    public Meta $meta;
    public array $items;

    public function setMeta(Response $req) {
        $this->meta = new Meta($req->http_success, $req->code, $req->data);
    }

    public function setItems(array $users, array $tags, array $music) {
        $this->items['users'] = $users;
        $this->items['tags'] = $tags;
        $this->items['music'] = $music;
    }

    public function fromCache(object $cache) {
        $this->meta = new Meta(true, 200, 'PLACEHOLDER');
        $this->setItems($cache->items->users, $cache->items->tags, $cache->items->music);
    }
}
