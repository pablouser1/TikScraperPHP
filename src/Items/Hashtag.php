<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Sender;

class Hashtag extends Base {
    function __construct(string $term, Sender $sender, Cache $cache) {
        parent::__construct($term, 'hashtag', $sender, $cache);
        if (!isset($this->info)) {
            $this->info();
        }
    }

    public function info(): self {
        $req = $this->sender->sendApi("/api/challenge/detail/", "www", [
            "challengeName" => $this->term
        ]);

        $res = new Info;
        $res->setMeta($req);

        if ($res->meta->success && isset($req->jsonBody->challengeInfo)) {
            $res->setDetail($req->jsonBody->challengeInfo->challenge);
            $res->setStats($req->jsonBody->challengeInfo->stats);
        }

        $this->info = $res;

        return $this;
    }

    public function feed(int $cursor = 0): self {
        $this->cursor = $cursor;

        if ($this->infoOk()) {
            $preloaded = $this->handleFeedCache();
            if (!$preloaded) {
                $query = [
                    "count" => 30,
                    "challengeID" => $this->info->detail->id,
                    "coverFormat" => 2,
                    "cursor" => $cursor,
                    "from_page" => "hashtag"
                ];
                $req = $this->sender->sendApi('/api/challenge/item_list/', 'www', $query);
                $response = new Feed;
                $response->fromReq($req, $cursor);
                $this->feed = $response;
            }
        }
        return $this;
    }
}
