<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Constants\TypeLegacy;
use TikScraper\Helpers\Curl;
use TikScraper\Models\Feed;
use TikScraper\Sender;

class Trending extends Base {
    function __construct(bool $legacy = false, Sender $sender, Cache $cache) {
        parent::__construct('', get_class($this), $legacy, $sender, $cache);
    }

    public function feed($cursor = 0): self {
        $this->cursor = $cursor;
        if ($this->canSendFeed()) {
            if ($this->legacy) {
                // Cache works for legacy mode only
                $cached = $this->handleFeedCache();
                if (!$cached) {
                    $this->feedLegacy($cursor);
                }
            } else {
                if ($this->cursor === 0) {
                    $ttwid = $this->__getTtwid();
                }
                $this->feedStandard($ttwid);
            }
            return $this;
        }
    }

    private function feedStandard(int $cursor = 0) {
        $query = [
            "count" => 30,
            "id" => 1,
            "sourceType" => 12,
            "itemID" => 1,
            "insertedItemID" => ""
        ];

        $req = $this->sender->sendApi('/api/recommend/item_list', 'm', $query, '', false, $cursor);
        $response = new Feed;
        $response->fromReq($req, null, $cursor);
        $this->feed = $response;
    }

    private function feedLegacy(int $cursor = 0) {
        $query = [
            "type" => TypeLegacy::TRENDING,
            "id" => 1,
            "count" => 30,
            "minCursor" => 0,
            "maxCursor" => $cursor
        ];

        $req = $this->sender->sendApi('/node/video/feed', 'm', $query, '', false, '', false);
        $response = new Feed;
        $response->fromReq($req, $cursor);
        $this->feed = $response;
    }

    private function __getTtwid(): string {
        $res = $this->sender->sendHead('https://www.tiktok.com');
        $cookies = Curl::extractCookies($res['data']);
        return $cookies['ttwid'] ?? '';
    }
}
