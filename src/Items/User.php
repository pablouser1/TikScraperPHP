<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Constants\StaticUrls;
use TikScraper\Helpers\Misc;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Sender;

class User extends Base {
    function __construct(string $term, Sender $sender, Cache $cache) {
        parent::__construct($term, 'user', $sender, $cache);
        if (!isset($this->info)) {
            $this->info();
        }
    }

    public function info() {
        $req = $this->sender->sendHTML("/@{$this->term}", 'www', [
            'lang' => 'en'
        ]);
        $info = new Info;
        $info->setMeta($req);
        if ($info->meta->success) {
            $jsonData = Misc::extractSigi($req->data);
            if (isset($jsonData->MobileUserModule)) {
                $this->sigi = $jsonData;
                $info->setDetail($jsonData->MobileUserModule->users->{$this->term});
                $info->setStats($jsonData->MobileUserModule->stats->{$this->term});
            }
        }
        $this->info = $info;
    }

    public function feed(int $cursor = 0): self {
        $this->cursor = $cursor;

        if ($this->infoOk()) {
            $preloaded = $this->handleFeedPreload('user-post');

            if (!$preloaded) {
                $query = [
                    "count" => 30,
                    "id" => $this->info->detail->id,
                    "cursor" => $cursor,
                    "type" => 1,
                    "secUid" => $this->info->detail->secUid,
                    "sourceType" => 8,
                    "appId" => 1233
                ];

                $req = $this->sender->sendApi('/api/post/item_list', 'm', $query, true, '', StaticUrls::USER_FEED);
                $response = new Feed;
                $response->fromReq($req, $cursor);
                $this->feed = $response;
            }
        }
        return $this;
    }
}
