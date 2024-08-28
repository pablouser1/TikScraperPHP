<?php
namespace TikScraper\Items;
use TikScraper\Cache;
use TikScraper\Constants\CachingMode;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Sender;

/**
 * For You Page
 */
class ForYou extends Base {
    protected CachingMode $caching_mode = CachingMode::NONE;

    function __construct(Sender $sender, Cache $cache) {
        parent::__construct('', 'foryou', $sender, $cache);
        if (!isset($this->info)) {
            $this->info();
        }
    }

    public function info(): self {
        // There is no info in For You, fill with some predefined data
        $info = Info::fromObj((object) [
            "detail" => (object) []
        ]);

        $this->info = $info;

        return $this;
    }

    /**
     * Feed for ForYou Page.
     * Data collection and feed personalization is DISABLED
     * @param int $cursor Not used (uses ttwid cookie)
     * @return \TikScraper\Items\ForYou
     */
    public function feed(int $cursor = 0): self {
        $this->cursor = $cursor;
        $res = $this->sender->sendApi('/recommend/item_list/', [
            'count' => 20,
            'from_page' => 'fyp'
        ]);

        $this->feed = Feed::fromReq($res);

        return $this;
    }
}
