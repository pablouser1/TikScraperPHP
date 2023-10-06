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

    public function info() {
        $req = $this->sender->sendHTML('/tag/' . $this->term, 'www', [
            'lang' => 'en'
        ]);
        $response = new Info;
        $response->setMeta($req);
        if ($response->meta->success) {
            $challengePage = null;

            // Get hashtag data from both SIGI and new Rehidrate
            if ($req->hasSigi || $req->hasRehidrate) {
                if (isset($req->sigiState->MobileChallengePage)) {
                    $challengePage = $req->sigiState->MobileChallengePage;
                } elseif (isset($req->sigiState->ChallengePage)) {
                    $challengePage = $req->sigiState->ChallengePage;
                } elseif (isset($req->rehidrateState->__DEFAULT_SCOPE__->{"desktop.challengePage.challengeDetail"})) {
                    $challengePage = $req->rehidrateState->__DEFAULT_SCOPE__->{"desktop.challengePage.challengeDetail"};
                }

                if ($challengePage) {
                    $this->state = $challengePage;
                    $response->setDetail($challengePage->challengeInfo->challenge);
                    $response->setStats($challengePage->challengeInfo->stats);
                }
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
                    "coverFormat" => 2,
                    "cursor" => $cursor,
                    "from_page" => "hashtag"
                ];
                $req = $this->sender->sendApi('/api/challenge/item_list', 'www', $query);
                $response = new Feed;
                $response->fromReq($req, $cursor);
                $this->feed = $response;
            }
        }
        return $this;
    }
}
