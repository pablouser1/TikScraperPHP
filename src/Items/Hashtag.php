<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Helpers\Misc;
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

    public function info() {
        $req = $this->sender->sendHTML('/tag/' . $this->term, 'www', [
            'lang' => 'en'
        ]);
        $response = new Info;
        $response->setMeta($req);
        if ($response->meta->success) {
            $jsonData = Misc::extractSigi($req->data);

            $challengePage = null;

            // Get hashtag data from SIGI JSON, support both mobile and desktop User-Agents
            if (isset($jsonData->MobileChallengePage)) {
                $challengePage = $jsonData->MobileChallengePage;
            } elseif (isset($jsonData->ChallengePage)) {
                $challengePage = $jsonData->ChallengePage;
            }

            if ($challengePage) {
                $this->sigi = $jsonData;
                $response->setDetail($challengePage->challengeInfo->challenge);
                $response->setStats($challengePage->challengeInfo->stats);
            }
        }
        $this->info = $response;
    }

    public function feed(int $cursor = 0): self {
        $this->cursor = $cursor;

        if ($this->infoOk()) {
            $preloaded = $this->handleFeedPreload('challenge');
            if (!$preloaded) {
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
        }
        return $this;
    }
}
