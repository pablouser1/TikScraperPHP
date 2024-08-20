<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Sender;

class Music extends Base {
    function __construct(string $name, Sender $sender, Cache $cache) {
        parent::__construct($name, 'music', $sender, $cache);
        if (!isset($this->info)) {
            $this->info();
        }
    }

    public function info(): self {
        $req = $this->sender->sendApi("/music/detail/", [
            'from_page' => 'music',
            'musicId' => $this->term
        ]);

        $info = Info::fromReq($req);
        if ($info->meta->success && isset($req->jsonBody->musicInfo)) {
            if (isset($req->jsonBody->musicInfo->music)) {
                $info->setDetail($req->jsonBody->musicInfo->music);
            }

            if (isset($req->jsonBody->musicInfo->stats)) {
                $info->setStats($req->jsonBody->musicInfo->stats);
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
                    "secUid" => "",
                    "musicID" => $this->info->detail->id,
                    "cursor" => $cursor,
                    "shareUid" => "",
                    "count" => 30
                ];
                $req = $this->sender->sendApi('/music/item_list/', $query);
                $this->feed = Feed::fromReq($req, $cursor);
            }
        }

        return $this;
    }
}
