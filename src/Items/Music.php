<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Helpers\Misc;
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
            $jsonData = Misc::extractSigi($req->data);

            $musicModule = null;

            // Get music data from SIGI JSON, support both mobile and desktop User-Agents
            if (isset($jsonData->MobileMusicModule)) {
                $musicModule = $jsonData->MobileMusicModule;
            } elseif (isset($jsonData->MusicModule)) {
                $musicModule = $jsonData->MusicModule;
            }

            if ($musicModule) {
                $this->sigi = $jsonData;
                $response->setDetail($musicModule->musicInfo->music);
                $response->setStats($musicModule->musicInfo->stats);
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
                $req = $this->sender->sendApi('/api/music/item_list', 'm', $query, true);
                $response = new Feed;
                $response->fromReq($req, $cursor);
                $this->feed = $response;
            }
        }
        return $this;
    }
}
