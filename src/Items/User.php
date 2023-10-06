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
            $uniqueId = null;

            if ($req->hasSigi) {
                // Get user data from SIGI JSON, support both mobile and desktop User-Agents
                if (isset($req->sigiState->MobileUserModule, $req->sigiState->MobileUserPage)) {
                    $userModule = $req->sigiState->MobileUserModule;
                    $uniqueId = $req->sigiState->MobileUserPage->uniqueId;
                } elseif (isset($req->sigiState->UserModule, $req->sigiState->UserPage)) {
                    $userModule = $req->sigiState->UserModule;
                    $uniqueId = $req->sigiState->UserPage->uniqueId;
                }

                if ($userModule) {
                    $finalUniqueId = $uniqueId ?? $this->term; // Use the user-provided term as fallback
                    $this->term = $finalUniqueId;
                    $this->state = $req->sigiState;
                    $info->setDetail($userModule->users->{$finalUniqueId});
                    $info->setStats($userModule->stats->{$finalUniqueId});
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
                    "count" => 30,
                    "coverFormat" => 2,
                    "cursor" => $cursor,
                    "from_page" => "user",
                    "secUid" => $this->info->detail->secUid
                ];

                $req = $this->sender->sendApi('/api/post/item_list', 'www', $query);
                $response = new Feed;
                $response->fromReq($req, $cursor);
                $this->feed = $response;
            }
        }
        return $this;
    }
}
