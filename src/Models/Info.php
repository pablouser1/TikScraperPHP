<?php
namespace TikScraper\Models;
use TikScraper\Constants\Responses;

class Info extends Base {
    public Meta $meta;
    public object $detail;
    public object $stats;

    public static function fromReq(Response $req): self {
        $info = new Info;
        $info->setMeta($req);
        return $info;
    }

    public static function fromCache(object $cache): self {
        $info = new Info;
        $info->setMeta(Responses::ok());
        if (isset($cache->meta->og)) {
            $info->meta->og = $cache->meta->og;
        }

        $info->setDetail($cache->detail);

        if (isset($cache->stats)) {
            $info->setStats($cache->stats);
        }

        return $info;
    }

    private function setMeta(Response $req): void {
        $this->meta = new Meta($req);
    }

    public function setDetail(object $detail): void {
        $this->detail = $detail;
    }

    public function setStats(object $stats): void {
        $this->stats = $stats;
    }
}
