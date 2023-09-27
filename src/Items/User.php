<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Constants\StaticUrls;
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
            $userModule = null;

            if ($req->hasSigi) {
                // Get user data from SIGI JSON, support both mobile and desktop User-Agents
                if (isset($req->sigiState->MobileUserModule)) {
                    $userModule = $req->sigiState->MobileUserModule;
                } elseif (isset($req->sigiState->UserModule)) {
                    $userModule = $req->sigiState->UserModule;
                }

                if ($userModule) {
                    $this->state = $req->sigiState;
                    $info->setDetail($userModule->users->{$this->term});
                    $info->setStats($userModule->stats->{$this->term});    
                }

            } elseif ($userModule === null && $req->hasRehidrate && isset($req->state->__DEFAULT_SCOPE__->{"webapp.user-detail"}->userInfo)) {
                $userModule = $req->rehidrateState->__DEFAULT_SCOPE__->{"webapp.user-detail"}->userInfo;
                $info->setDetail($userModule->user);
                $info->setStats($userModule->stats);
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
                    "WebIdLastTime" => time(),
                    "count" => 30,
                    "coverFormat" => 2,
                    "cursor" => $cursor,
                    "secUid" => $this->info->detail->secUid
                ];

                $req = $this->sender->sendApi('/api/post/item_list', 'm', $query, true, null, StaticUrls::USER_FEED);
                $response = new Feed;
                $response->fromReq($req, $cursor);
                $this->feed = $response;
            }
        }
        return $this;
    }
}
