<?php
namespace TikScraper\Items;

use TikScraper\Cache;
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

    public function info(): self {
        $req = $this->sender->sendHTML("/@{$this->term}", 'www');
        $info = new Info;
        $info->setMeta($req);
        if ($info->meta->success) {
            if ($req->hasRehidrate() && isset($req->rehidrateState->__DEFAULT_SCOPE__->{"webapp.user-detail"}->userInfo)) {
                $userModule = $req->rehidrateState->__DEFAULT_SCOPE__->{"webapp.user-detail"}->userInfo;
                $info->setDetail($userModule->user);
                $info->setStats($userModule->stats);
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
                    "count" => 35,
                    "coverFormat" => 2,
                    "cursor" => $cursor,
                    "from_page" => "user",
                    "history_len" => 5,
                    "secUid" => $this->info->detail->secUid
                ];

                $req = $this->sender->sendApi('/api/post/item_list/', 'www', $query);
                $response = new Feed;
                $response->fromReq($req, $cursor);
                $this->feed = $response;
            }
        }
        return $this;
    }
}
