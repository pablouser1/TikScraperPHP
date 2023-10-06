<?php
namespace TikScraper\Items;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use TikScraper\Cache;
use TikScraper\Models\Feed;
use TikScraper\Sender;

class Trending extends Base {
    function __construct(Sender $sender, Cache $cache) {
        parent::__construct('', 'trending', $sender, $cache);
    }

    public function feed($cursor = ""): self {
        $this->cursor = $cursor;
        $cookie = null;
        if (!$this->cursor) {
            $cookie = $this->__getTtwid();
            $this->cursor = $cookie->getValue();
        }

        $query = [
            "count" => 30,
            "id" => 1,
            "sourceType" => 12,
            "itemID" => 1,
            "insertedItemID" => ""
        ];

        $req = $this->sender->sendApi('/api/recommend/item_list', 'www', $query, $cookie);
        $response = new Feed;
        $response->fromReq($req, null, $this->cursor);
        $this->feed = $response;
        return $this;
    }

    private function __getTtwid(): ?SetCookie {
        $jar = new CookieJar;
        $this->sender->sendHead('https://www.tiktok.com/', $jar);
        $cookie = $jar->getCookieByName("ttwid");

        return $cookie;
    }
}
