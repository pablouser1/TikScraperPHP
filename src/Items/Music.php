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
        $req = $this->sender->sendApi("/api/music/detail/", 'www', [
            'from_page' => 'music',
            'musicId' => $this->term
        ]);

        $res = new Info;
        $res->setMeta($req);

        if ($res->meta->success && isset($req->jsonBody->musicInfo)) {
            $res->setDetail($req->jsonBody->musicInfo->music);
            $res->setStats($req->jsonBody->musicInfo->stats);
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
                    "secUid" => "",
                    "musicID" => $this->info->detail->id,
                    "cursor" => $cursor,
                    "shareUid" => "",
                    "count" => 30,
                ];
                $req = $this->sender->sendApi('/api/music/item_list/', 'www', $query);
                $response = new Feed;
                $response->fromReq($req, $cursor);
                $this->feed = $response;
            }
        }

        return $this;
    }
}
