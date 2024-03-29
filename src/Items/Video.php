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

    public function info(): self {
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
            if ($req->hasRehidrate() && isset($req->rehidrateState->__DEFAULT_SCOPE__->{'webapp.video-detail'})) {
                $root = $req->rehidrateState->__DEFAULT_SCOPE__->{'webapp.video-detail'};
                $this->state = $req->rehidrateState;
                $this->item = $root->itemInfo->itemStruct;
                $response->setDetail($this->item->author);
                $response->setStats($this->item->stats);
            }
        }
        $this->info = $response;

        return $this;
    }

    public function feed(): self {
        $this->cursor = 0;
        if ($this->item !== null) {
            $response = new Feed;
            $response->setItems([$this->item]);
            $response->setNav(false, null, '');
            $response->setMeta(Responses::ok());
            $this->feed = $response;
        }
        return $this;
    }
}
