<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Helpers\Misc;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Sender;

class Hashtag extends Base {
    function __construct(string $term, Sender $sender, Cache $cache, bool $legacy = false) {
        parent::__construct($term, 'hashtag', $sender, $cache, $legacy);
        if (!isset($this->info)) {
            $this->info();
        }
    }

    public function info() {
        $req = $this->sender->sendHTML('/tag/' . $this->term, 'www', [
            'lang' => 'en'
        ]);
        $response = new Info;
        $response->setMeta($req);
        if ($response->meta->success) {
            $jsonData = Misc::extractSigi($req->data);
            if (isset($jsonData->ChallengePage)) {
                $response->setDetail($jsonData->ChallengePage->challengeInfo->challenge);
                $response->setStats($jsonData->ChallengePage->challengeInfo->stats);
            }
        }
        $this->info = $response;
    }

    public function feed(int $cursor = 0): self {
        $this->cursor = $cursor;
        $cached = $this->handleFeedCache();
        if (!$cached && $this->canSendFeed()) {
            $query = [
                "count" => 30,
                "challengeID" => $this->info->detail->id,
                "cursor" => $cursor
            ];
            $req = $this->sender->sendApi('/api/challenge/item_list', 'm', $query);
            $response = new Feed;
            $response->fromReq($req, $cursor);
            $this->feed = $response;
        }
        return $this;
    }
}
