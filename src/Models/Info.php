<?php
namespace TikScraper\Models;
use TikScraper\Constants\Responses;

class Info extends Base {
    public Meta $meta;
    public object $detail;
    public object $stats;

    public function setMeta(Response $req): void {
        $this->meta = new Meta($req);
    }

    public function setDetail(object $detail): void {
        $this->detail = $detail;
    }

    public function setStats(object $stats): void {
        $this->stats = $stats;
    }

    public function fromCache(object $cache): void {
        $this->meta = new Meta(Responses::ok());
        if (isset($cache->meta->og)) {
            $this->meta->og = $cache->meta->og;
        }
        $this->setDetail($cache->detail);
        if (isset($cache->stats)) {
            $this->setStats($cache->stats);
        }
    }
}
