<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Constants\Responses;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Sender;

class Video extends Base {
    private ?object $item = null;

    function __construct(string $term, Sender $sender, Cache $cache) {
        parent::__construct($term, 'video', $sender, $cache);
        if (!isset($this->info)) {
            $this->info();
        }
    }

    public function info() {
        $subdomain = '';
        $endpoint = '';
        if (is_numeric($this->term)) {
            $subdomain = 'm';
            $endpoint = '/v/' . $this->term;
        } else {
            $subdomain = 'www';
            $endpoint = '/t/' . $this->term;
        }

        $req = $this->sender->sendHTML($endpoint, $subdomain);
        $response = new Info;
        $response->setMeta($req);
        if ($response->meta->success) {
            if ($req->hasSigi) {
                // Try to get video info from exclusive SharingVideoModule
                if (isset($req->sigiState->SharingVideoModule)) {
                    $this->state = $req->sigiState;
                    $this->item = $req->sigiState->SharingVideoModule->videoData->itemInfo->itemStruct;
                    $response->setDetail($this->item->author);
                    $response->setStats($this->item->authorStats);
                // Try to get video info from common UserModule
                } else {
                    $userModule = null;
                    if (isset($req->sigiState->UserModule)) {
                        $userModule = $req->sigiState->UserModule;
                    } elseif (isset($req->sigiState->MobileUserModule)) {
                        $userModule = $req->sigiState->MobileUserModule;
                    }

                    if ($userModule !== null) {
                        $this->state = $req->sigiState;
                        $objIterator = new \ArrayIterator($userModule->users);
                        $user = $objIterator->current();
                        $response->setDetail($user);
                        // $response->setStats... is not used, for some reason $userModule->stats is empty (at least for now)
                    }
                }
            } elseif ($req->hasRehidrate && isset($req->rehidrateState->__DEFAULT_SCOPE__->{'webapp.video-detail'})) {
                $root = $req->rehidrateState->__DEFAULT_SCOPE__->{'webapp.video-detail'};
                $this->state = $req->rehidrateState;
                $this->item = $root->itemInfo->itemStruct;
                $response->setDetail($this->item->author);
                $response->setStats($this->item->stats);
            }
        }
        $this->info = $response;
    }

    public function feed(): self {
        $this->cursor = 0;
        if ($this->item !== null) {
            // Get feed using SharingVideoModule method
            $response = new Feed;
            $response->setItems([$this->item]);
            $response->setNav(false, null, '');
            $response->setMeta(Responses::ok());
            $this->feed = $response;
        } else {
            // Get feed using standard UserModule / ItemModule method
            $this->handleFeedPreload("video");
        }
        return $this;
    }
}
