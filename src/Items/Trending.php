<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Models\Feed;
use TikScraper\Sender;

class Trending extends Base {
    function __construct(Sender $sender, Cache $cache) {
        parent::__construct('', 'trending', $sender, $cache);
    }

    public function feed($cursor = ""): self {
        $this->cursor = $cursor;
        if (!$this->cursor) {
            $this->cursor = $this->__getTtwid();
        }
        $query = [
            "count" => 30,
            "id" => 1,
            "sourceType" => 12,
            "itemID" => 1,
            "insertedItemID" => ""
        ];

        $req = $this->sender->sendApi('/api/recommend/item_list', 'm', $query, false, $this->cursor);
        $response = new Feed;
        $response->fromReq($req, null, $this->cursor);
        $this->feed = $response;
        return $this;
    }

    private function __getTtwid(): string {
        $res = $this->sender->sendHead('https://www.tiktok.com/');
        return $res['cookies']['ttwid'] ?? '';
    }
}
