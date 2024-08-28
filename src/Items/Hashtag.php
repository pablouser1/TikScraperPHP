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
        $req = $this->sender->sendApi("/challenge/detail/", [
            "challengeName" => $this->term
        ], "/tag/" . $this->term);

        $info = Info::fromReq($req);
        if ($info->meta->success && isset($req->jsonBody->challengeInfo)) {
            if (isset($req->jsonBody->challengeInfo->challenge)) {
                $info->setDetail($req->jsonBody->challengeInfo->challenge);
            }

            if (isset($req->jsonBody->challengeInfo->stats)) {
                $info->setStats($req->jsonBody->challengeInfo->stats);
            }
        }

        $this->info = $info;

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
                $req = $this->sender->sendApi('/challenge/item_list/', $query, "/tag/" . $this->term);
                $this->feed = Feed::fromReq($req);
            }
        }
        return $this;
    }
}
