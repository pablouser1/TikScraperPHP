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

    public function info() {
        $req = $this->sender->sendHTML('/music/' . $this->term, 'www', [
            'lang' => 'en'
        ]);
        $response = new Info;
        $response->setMeta($req);
        if ($response->meta->success) {
            $musicModule = null;

            // Get hashtag data from both SIGI and new Rehidrate
            if ($req->hasSigi || $req->hasRehidrate) {
                if (isset($req->sigiState->MobileMusicModule)) {
                    $musicModule = $req->sigiState->MobileMusicModule;
                } elseif (isset($req->sigiState->MusicModule)) {
                    $musicModule = $req->sigiState->MusicModule;
                } elseif (isset($req->rehidrateState->__DEFAULT_SCOPE__->{"desktop.musicPage.musicDetail"})) {
                    $musicModule = $req->rehidrateState->__DEFAULT_SCOPE__->{"desktop.musicPage.musicDetail"};
                }

                if ($musicModule) {
                    $this->state = $musicModule;
                    $response->setDetail($musicModule->musicInfo->music);
                    $response->setStats($musicModule->musicInfo->stats);
                }
            }
        }
        $this->info = $response;
    }

    public function feed(int $cursor = 0): self {
        $this->cursor = $cursor;

        if ($this->infoOk()) {
            $preloaded = $this->handleFeedPreload('music');
            if (!$preloaded) {
                $query = [
                    "secUid" => "",
                    "musicID" => $this->info->detail->id,
                    "cursor" => $cursor,
                    "shareUid" => "",
                    "count" => 30,
                ];
                $req = $this->sender->sendApi('/api/music/item_list', 'www', $query);
                $response = new Feed;
                $response->fromReq($req, $cursor);
                $this->feed = $response;
            }
        }
        return $this;
    }
}
