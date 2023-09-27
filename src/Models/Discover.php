<?php
namespace TikScraper\Models;
use TikScraper\Constants\Responses;

class Discover extends Base {
    public Meta $meta;
    public array $items;

    public function setMeta(Response $req) {
        $this->meta = new Meta($req);
    }

    public function setItems(array $users, array $tags, array $music) {
        $this->items['users'] = $users;
        $this->items['tags'] = $tags;
        $this->items['music'] = $music;
    }

    public function fromCache(object $cache) {
        $this->meta = new Meta(Responses::ok());
        $this->setItems($cache->items->users, $cache->items->tags, $cache->items->music);
    }
}
