<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Constants\TypeLegacy;
use TikScraper\Helpers\Misc;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Sender;
use TikScraper\Constants\StaticUrls;

class User extends Base {
    function __construct(string $term, Sender $sender, Cache $cache, bool $legacy = false) {
        parent::__construct($term, 'user', $sender, $cache, $legacy);
        if (!isset($this->info)) {
            $this->info();
        }
    }

    public function info() {
        $req = $this->sender->sendHTML("/@{$this->term}", 'www', [
            'lang' => 'en'
        ]);
        $response = new Info;
        $response->setMeta($req);
        if ($response->meta->success) {
            $jsonData = Misc::extractSigi($req->data);
            if (isset($jsonData->UserModule)) {
                $response->setDetail($jsonData->UserModule->users->{$this->term});
                $response->setStats($jsonData->UserModule->stats->{$this->term});
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
            "count" => 30,
            "id" => $this->info->detail->id,
            "cursor" => $cursor,
            "type" => 1,
            "secUid" => $this->info->detail->secUid,
            "sourceType" => 8,
            "appId" => 1233
        ];

        $req = $this->sender->sendApi('/api/post/item_list', 'm', $query, StaticUrls::USER_FEED, true);
        $response = new Feed;
        $response->fromReq($req, $cursor);
        $this->feed = $response;
    }

    private function feedLegacy(int $cursor = 0) {
        $query = [
            "type" => TypeLegacy::USER,
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
