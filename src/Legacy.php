<?php
namespace TikScraper;
use TikScraper\Models\Discover;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;

class Legacy extends Api {
    const MODE = 'LEGACY';
    function __construct(array $config = [], $cache_engine = null) {
        if (!isset($config['user_agent'])) {
            $config['user_agent'] = Common::LEGACY_USERAGENT;
        }
        $config['signer'] = [];
        parent::__construct($config, $cache_engine);
    }

    public function getTrending($cursor = 0): Feed {
        $query = [
            "type" => 5,
            "id" => 1,
            "count" => 30,
            "minCursor" => 0,
            "maxCursor" => $cursor
        ];

        $req = $this->sender->sendApi('/node/video/feed', 'm', $query, '', false, '', false);
        $response = new Feed;
        $response->fromReq($req, $cursor);
        return $response;
    }

    public function getUserFeed(string $username, int $cursor = 0): Feed {
        $cache_key = 'user-' . $username . '-feed-' . $cursor . '-legacy';
        if ($this->cache->exists($cache_key)) return $this->cache->handleFeed($cache_key);

        $user = $this->getUser($username);
        if ($user->meta->success) {
            $query = [
                "type" => 1,
                "id" => $user->detail->id,
                "count" => 30,
                "minCursor" => 0,
                "maxCursor" => $cursor
            ];

            $req = $this->sender->sendApi('/node/video/feed', 'm', $query, '', false, '', false);
            $response = new Feed;
            $response->fromReq($req, $cursor);
            $response->setInfo($user);

            if ($response->meta->success) {
                $this->cache->set($cache_key, $response->ToJson());
            }

            return $response;
        }
        return $this->__buildErrorFeed($user);
    }

    public function getHashtagFeed(string $hashtag, int $cursor = 0): Feed {
        $cache_key = 'hashtag-' . $hashtag . '-feed-' . $cursor . '-legacy';
        if ($this->cache->exists($cache_key)) return $this->cache->handleFeed($cache_key);

        $hashtag = $this->getHashtag($hashtag);
        if ($hashtag->meta->success) {
            $id = $hashtag->detail->id;
            $query = [
                "type" => 3,
                "id" => $id,
                "count" => 30,
                "minCursor" => 0,
                "maxCursor" => $cursor
            ];
            $req = $this->sender->sendApi('/node/video/feed', 'm', $query, '', false, '', false);
            $response = new Feed;
            $response->fromReq($req, $cursor);
            $response->setInfo($hashtag);

            if ($response->meta->success) $this->cache->set($cache_key, $response->ToJson());
            return $response;
        }
        return $this->__buildErrorFeed($hashtag);
    }

    public function getMusic(string $music_id): Info {
        $cache_key = 'music- ' . $music_id;
        if ($this->cache->exists($cache_key)) return $this->cache->handleInfo($cache_key);

        $req = $this->sender->sendApi("/node/share/music/{$music_id}", 'm', [], '', false, '', false);
        $result = new Info;
        $result->setMeta($req);
        if ($result->meta->success) {
            $result->setDetail($req->data->musicInfo->music);
            $result->setStats($req->data->musicInfo->stats);

            $this->cache->set($cache_key, $result->ToJson());
        }
        return $result;
    }

    public function getMusicFeed(string $music_id, int $cursor = 0): Feed {
        $cache_key = 'music' . $music_id . '-feed-' . $cursor;
        if ($this->cache->exists($cache_key)) return $this->cache->handleFeed($cache_key);

        $music = $this->getMusic($music_id);
        if ($music->meta->success) {
            $query = [
                "type" => 4,
                "id" => $music->detail->id,
                "count" => 30,
                "minCursor" => 0,
                "maxCursor" => $cursor
            ];
            $req = $this->sender->sendApi('/node/video/feed', 'm', $query, '', false, '', false);
            $response = new Feed;
            $response->fromReq($req, $cursor);
            $response->setInfo($music);
            if ($response->meta->success) {
                $this->cache->set($cache_key, $response->ToJson());
            }

            return $response;

        }
        return $this->__buildErrorFeed($music);
    }

    public function getDiscover(): Discover {
        $cacheKey = 'discover';
        if ($this->cache->exists($cacheKey)) return $this->cache->handleDiscover($cacheKey);
        $query = [
            'userCount' => 30,
            'from_page' => 'discover'
        ];
        $req = $this->sender->sendApi('/node/share/discover', 'm', $query, '', false, '', false);
        $response = new Discover;
        $response->setMeta($req);
        if ($response->meta->success) {
            $response->setItems(
                $req->data->body[0]->exploreList,
                $req->data->body[1]->exploreList,
                $req->data->body[2]->exploreList
            );
            $this->cache->set($cacheKey, $response->ToJson());
        }
        return $response;
    }
}
