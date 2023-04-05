<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Helpers\Misc;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Models\Response;
use TikScraper\Sender;

class Video extends Base {
    private object $item;

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
            $jsonData = Misc::extractSigi($req->data);
            if (isset($jsonData->SharingVideoModule)) {
                $this->item = $jsonData->SharingVideoModule->videoData->itemInfo->itemStruct;
                $response->setDetail($this->item->author);
                $response->setStats($this->item->authorStats);
            }

            $sharingComment = null;

            // Get video comments data from SIGI JSON, support both mobile and desktop User-Agents
            if (isset($jsonData->MobileSharingComment)) {
                $sharingComment = $jsonData->MobileSharingComment;
            } elseif (isset($jsonData->SharingComment)) {
                $sharingComment = $jsonData->SharingComment;
            }

            if (isset($sharingComment)) {
                $this->item->comments = $sharingComment->comments;
            }
        }
        $this->info = $response;
    }

    public function feed(): self {
        $this->cursor = 0;
        $cached = $this->handleFeedCache();
        if (!$cached && $this->infoOk()) {
            $response = new Feed;
            $response->setItems([$this->item]);
            $response->setNav(false, null, '');
            $response->setMeta(new Response(200, "PLACEHOLDER"));
            $this->feed = $response;
        }
        return $this;
    }
}
