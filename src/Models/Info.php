<?php
namespace TikScraper\Models;

class Info extends Base {
    public Meta $meta;
    public object $detail;
    public object $stats;

    public function setMeta(Response $req) {
        $this->meta = new Meta($req->http_success, $req->code, $req->data);
    }

    public function setDetail(object $detail) {
        $this->detail = $detail;
    }

    public function setStats(object $stats) {
        $this->stats = $stats;
    }

    public function fromCache(object $cache) {
        $this->setMeta(new Response(200, 'PLACEHOLDER'));
        if (isset($cache->meta->og)) {
            $this->meta->og = $cache->meta->og;
        }
        $this->setDetail($cache->detail);
        $this->setStats($cache->stats);
    }
}
