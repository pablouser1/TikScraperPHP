<?php
namespace TikScraper;

use TikScraper\Interfaces\ICache;
use TikScraper\Items\Following;
use TikScraper\Items\ForYou;
use TikScraper\Items\User;
use TikScraper\Items\Hashtag;
use TikScraper\Items\Music;
use TikScraper\Items\Video;

/**
 * Main Class of the library
 */
class Api {
    private Sender $sender;
    private Cache $cache;

    function __construct(array $config = [], ?ICache $cache_engine = null) {
        $this->sender = new Sender($config);
        $this->cache = new Cache($cache_engine);
    }

    // -- Main methods -- //
    /**
     * Gets user from username (@...)
     * @param string $term Username
     * @return \TikScraper\Items\User
     */
    public function user(string $term): User {
        return new User($term, $this->sender, $this->cache);
    }

    /**
     * Gets hashtag from name.
     * Also known as tag or challenge
     * @param string $term Hashtag name
     * @return \TikScraper\Items\Hashtag
     */
    public function hashtag(string $term): Hashtag {
        return new Hashtag($term, $this->sender, $this->cache);
    }

    /**
     * Gets videos that use a specific song
     * @param string $term Song ID
     * @return \TikScraper\Items\Music
     */
    public function music(string $term): Music {
        return new Music($term, $this->sender, $this->cache);
    }

    /**
     * Gets video from ID, supports both Webapp and phone
     * @param string $term ID
     * @return \TikScraper\Items\Video
     */
    public function video(string $term): Video {
        return new Video($term, $this->sender, $this->cache);
    }

    /**
     * Gets for you feed.
     * @return \TikScraper\Items\ForYou
     */
    public function foryou(): ForYou {
        return new ForYou($this->sender, $this->cache);
    }

    /**
     * Gets recommended users
     * @return \TikScraper\Items\Following
     */
    public function following(): Following {
        return new Following($this->sender, $this->cache);
    }
}
