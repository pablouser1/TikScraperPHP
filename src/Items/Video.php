<?php
namespace TikScraper\Items;

use TikScraper\Cache;
use TikScraper\Helpers\Misc;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Sender;

class Video extends Base {
    private object $item;
    function __construct(string $term, bool $legacy = false, Sender $sender, Cache $cache) {
        parent::__construct($term, get_class($this), $legacy, $sender, $cache);
        $this->info();
    }

    public function info() {
        $subdomain = '';
        $endpoint = '';
        if (is_numeric($this->term)) {
            $subdomain = 'm';
            $endpoint = '/v/' . $this->term;
        } else {
            $subdomain = 'vm';
            $endpoint = '/' . $this->term;
        }

        $req = $this->sender->sendHTML($endpoint, $subdomain, []);
        $response = new Info;
        $response->setMeta($req);
        if ($response->meta->success) {
            $jsonData = Misc::extractSigi($req->data);
            if (isset($jsonData->ItemModule, $jsonData->ItemList, $jsonData->UserModule)) {
                $this->term = $jsonData->ItemList->video->keyword;
                $this->item = $jsonData->ItemModule->{$this->term};
                $author = $this->item->author;
                $response->setDetail($jsonData->UserModule->users->{$author});
                $response->setStats($this->item->stats);
            }
        }
        $this->info = $response;
    }

    public function feed(): self {
        $this->cursor = 0;
        $response = new Feed;
        $response->setItems([$this->item]);
        $response->setNav(false, null, '');
        $this->feed = $response;
        return $this;
    }
}
