<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Constants\TypeLegacy;
use TikScraper\Helpers\Misc;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Sender;

class Music extends Base {
    function __construct(string $name, bool $legacy = false, Sender $sender, Cache $cache) {
        parent::__construct($name, get_class($this), $legacy, $sender, $cache);
        if (!isset($this->info)) {
            $this->info();
        }
    }

    public function info() {
        $req = $this->sender->sendHTML('/music/' . $this->term, 'www', [
            'lang' => 'en'
        ]);
        $response = new Info;
        $response->setMeta($req);
        if ($response->meta->success) {
            $jsonData = Misc::extractSigi($req->data);
            if (isset($jsonData->MusicModule)) {
                $response->setDetail($jsonData->MusicModule->musicInfo->music);
                $response->setStats($jsonData->MusicModule->musicInfo->stats);
            }
        }
        $this->info = $response;
    }

    public function feed(int $cursor = 0): self {
        $this->cursor = $cursor;
        $cached = $this->handleFeedCache();
        if (!$cached && $this->canSendFeed()) {
            if ($this->legacy) {
                $this->feedLegacy($cursor);
            } else {
                $this->feedStandard($cursor);
            }
        }
        return $this;
    }

    private function feedStandard(int $cursor = 0) {
        $query = [
            "secUid" => "",
            "musicID" => $this->info->detail->id,
            "cursor" => $cursor,
            "shareUid" => "",
            "count" => 30,
        ];
        $req = $this->sender->sendApi('/api/music/item_list', 'm', $query, '', true);
        $response = new Feed;
        $response->fromReq($req, $cursor);
        $this->feed = $response;
    }

    private function feedLegacy(int $cursor = 0) {
        $query = [
            "type" => TypeLegacy::MUSIC,
            "id" => $this->info->detail->id,
            "count" => 30,
            "minCursor" => 0,
            "maxCursor" => $cursor
        ];
        $req = $this->sender->sendApi('/node/video/feed', 'm', $query, '', false, '', false);
        $response = new Feed;
        $response->fromReq($req, $cursor);
        $this->feed = $response;
    }
}
