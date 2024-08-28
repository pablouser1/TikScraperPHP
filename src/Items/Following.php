<?php
namespace TikScraper\Items;
use TikScraper\Cache;
use TikScraper\Constants\CachingMode;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Sender;

/**
 * Discover Item, named "Following" everywhere in TikTok except the endpoint
 */
class Following extends Base {
    protected CachingMode $caching_mode = CachingMode::FEED_ONLY;

    function __construct(Sender $sender, Cache $cache) {
        parent::__construct('user', 'following', $sender, $cache);
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
     * Feed for Following Page
     * @param int $cursor Not used (uses ttwid cookie)
     * @return \TikScraper\Items\ForYou
     */
    public function feed(int $cursor = 0): self {
        $this->cursor = $cursor;

        $res = $this->sender->sendApi('/discover/user/', [
            'count' => 20,
            'data_collection_enabled' => false,
            'discoverType' => 0,
            'from_page' => 'following',
            'needItemList' => true,
            'keyWord' => '',
            'offset' => $cursor,
            'useRecommend' => false
        ]);

        $this->feed = Feed::fromReq($res);

        return $this;
    }
}
