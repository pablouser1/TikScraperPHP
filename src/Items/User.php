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
        $req = $this->sender->sendApi("/user/detail/", [
            "abTestVersion" => "[object Object]",
            "appType" => "m",
            "secUid" => "",
            "uniqueId" => $this->term
        ], "/@" . $this->term);

        $info = Info::fromReq($req);
        if ($info->meta->success) {
            if (isset($req->jsonBody->userInfo, $req->jsonBody->userInfo->user)) {
                // userInfo is available
                if (isset($req->jsonBody->userInfo->user)) {
                    // Set details
                    $info->setDetail($req->jsonBody->userInfo->user);
                }

                if (isset($req->jsonBody->userInfo->stats)) {
                    // Set stats
                    $info->setStats($req->jsonBody->userInfo->stats);
                }
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
                    "needPinnedItemIds" => "true",
                    "post_item_list_request_type" => 0,
                    "secUid" => $this->info->detail->secUid,
                    "userId" => $this->info->detail->id
                ];

                $req = $this->sender->sendApi('/post/item_list/', $query, "/@" . $this->term);
                $this->feed = Feed::fromReq($req);
            }
        }
        return $this;
    }
}
