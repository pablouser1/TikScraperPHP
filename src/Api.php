<?php

namespace TikScraper;

use TikScraper\Helpers\Curl;
use TikScraper\Helpers\Misc;
use TikScraper\Models\Discover;
use TikScraper\Models\Feed;
use TikScraper\Models\Info;
use TikScraper\Models\Response;

class Api {
    private Sender $sender;
    private Cache $cache;

    function __construct(array $config = [], mixed $cache_engine = false) {
        $this->sender = new Sender($config);
        $this->cache = new Cache($cache_engine);
    }

    public function getTrending(string $ttwid = ''): Feed {
        if (!$ttwid) {
            $ttwid = $this->__getTtwid();
        }

        $query = [
            "count" => 30,
            "id" => 1,
            "sourceType" => 12,
            "itemID" => 1,
            "insertedItemID" => "",
        ];

        $req = $this->sender->sendGet('/api/recommend/item_list', 'm', $query, true, false, $ttwid);
        $response = new Feed;
        $response->fromReq($req, null, $ttwid);
        return $response;
    }

    // -- Main methods -- //
    public function getUser(string $username): Info {
        $username = urlencode($username);
        $cache_key = 'user-' . $username;
        if ($this->cache->exists($cache_key)) return $this->cache->handleInfo($cache_key);

        $req = $this->sender->sendGet("/@{$username}/?lang=en", 'www', [], false);
        $response = new Info;
        $response->setMeta($req);
        if ($response->meta->success) {
            $json_string = Misc::string_between($req->data, "window['SIGI_STATE']=", ";window['SIGI_RETRY']=");
            $jsonData = json_decode($json_string);
            if (isset($jsonData->UserModule)) {
                $response->setDetail($jsonData->UserModule->users->{$username});
                $response->setStats($jsonData->UserModule->stats->{$username});
                $this->cache->set($cache_key, $response->ToJson());
            }
        }
        return $response;
    }

    public function getUserFeed(string $username, int $cursor = 0): Feed {
        $cache_key = 'user-' . $username . '-feed-' . $cursor;
        if ($this->cache->exists($cache_key)) return $this->cache->handleFeed($cache_key);

        $user = $this->getUser($username);
        if ($user->meta->success) {
            $id = $user->detail->id;
            $secUid = $user->detail->secUid;
            $query = [
                "count" => 30,
                "id" => $id,
                "cursor" => $cursor,
                "type" => 1,
                "secUid" => $secUid,
                "sourceType" => 8,
                "appId" => 1233
            ];

            $req = $this->sender->sendGet('/api/post/item_list/', 'm', $query, true, true);
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

    public function getHashtag(string $hashtag): Info {
        $cache_key = 'hashtag-' . $hashtag;
        if ($this->cache->exists($cache_key)) return $this->cache->handleInfo($cache_key);

        $query = [
            "challengeName" => $hashtag
        ];
        $endpoint = '/api/challenge/detail/';
        $req = $this->sender->sendGet($endpoint, 'm', $query);
        $response = new Info;
        $response->setMeta($req);
        if ($response->meta->success) {
            $response->setDetail($req->data->challengeInfo->challenge);
            $response->setStats($req->data->challengeInfo->stats);
            $this->cache->set($cache_key, $response->ToJson());
        }
        return $response;
    }

    public function getHashtagFeed(string $hashtag, int $cursor = 0): Feed {
        $cache_key = 'hashtag-' . $hashtag . '-feed-' . $cursor;
        if ($this->cache->exists($cache_key)) return $this->cache->handleFeed($cache_key);

        $hashtag = $this->getHashtag($hashtag);
        if ($hashtag->meta->success) {
            $id = $hashtag->detail->id;
            $query = [
                "count" => 30,
                "challengeID" => $id,
                "cursor" => $cursor
            ];
            $req = $this->sender->sendGet('/api/challenge/item_list', 'm', $query);
            $response = new Feed;
            $response->fromReq($req, $cursor);
            $response->setInfo($hashtag);

            if ($response->meta->success) {
                $this->cache->set($cache_key, $response->ToJson());
            }
            return $response;
        }
        return $this->__buildErrorFeed($hashtag);
    }

    public function getMusic(string $music_id): Info {
        $cache_key = 'music- ' . $music_id;
        if ($this->cache->exists($cache_key)) return $this->cache->handleInfo($cache_key);

        $req = $this->sender->sendGet("/music/{$music_id}", 'www', [], false);
        $result = new Info;
        $result->setMeta($req);
        if ($result->meta->success) {
            $json_string = Misc::string_between($req->data, "window['SIGI_STATE']=", ";window['SIGI_RETRY']=");
            $jsonData = json_decode($json_string);
            if (isset($jsonData->MusicModule)) {
                $result->setDetail($jsonData->MusicModule->musicInfo->music);
                $result->setStats($jsonData->MusicModule->musicInfo->stats);

                $this->cache->set($cache_key, $result->ToJson());
            }
        }
        return $result;
    }

    public function getMusicFeed(string $music_id, int $cursor = 0): Feed {
        $cache_key = 'music' . $music_id . '-feed-' . $cursor;
        if ($this->cache->exists($cache_key)) return $this->cache->handleFeed($cache_key);

        $music = $this->getMusic($music_id);
        if ($music->meta->success) {
            $query = [
                "secUid" => "",
                "musicID" => $music->detail->id,
                "cursor" => $cursor,
                "shareUid" => "",
                "count" => 30,
            ];
            $req = $this->sender->sendGet('/api/music/item_list/', 'm', $query, true, true);
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
    /**
     * Get video by video id
     * Accept video ID and returns video detail object
     */
    public function getVideoByID(string $video_id): Feed {
        $cache_key = 'video-' . $video_id;
        if ($this->cache->exists($cache_key)) return $this->cache->handleFeed($cache_key);

        $subdomain = '';
        $endpoint = '';
        if (is_numeric($video_id)) {
            $subdomain = 'm';
            $endpoint = '/v/' . $video_id;
        } else {
            $subdomain = 'vm';
            $endpoint = '/' . $video_id;
        }

        $req = $this->sender->sendGet($endpoint, $subdomain, [], false);
        $response = new Feed;
        $response->setMeta($req);
        if ($response->meta->success) {
            $json_string = Misc::string_between($req->data, "window['SIGI_STATE']=", ";window['SIGI_RETRY']=");
            $jsonData = json_decode($json_string);
            if (isset($jsonData->ItemModule, $jsonData->ItemList, $jsonData->UserModule)) {
                $id = $jsonData->ItemList->video->keyword;
                $item = $jsonData->ItemModule->{$id};
                $username = $item->author;

                $response->setItems([$item]);
                $response->setNav(false, null, '');
                $info = new Info;
                $info->setDetail($jsonData->UserModule->users->{$username});
                $info->setStats($item->stats);
                $response->setInfo($info);
                $this->cache->set($cache_key, $response->ToJson());
            }
        }
        return $response;
    }

    public function getDiscover(): Discover {
        $cacheKey = 'discover';
        if ($this->cache->exists($cacheKey)) return $this->cache->handleDiscover($cacheKey);
        $query = [
            'userCount' => 30,
            'from_page' => 'discover',
            'device_id' => Misc::makeId()
        ];
        $req = $this->sender->sendGet('/node/share/discover', 'www', $query, false);
        $req->data = json_decode($req->data); // Temp workaround
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

    // Misc
    private function __buildErrorFeed(Info $info): Feed {
        $meta = $info->meta;
        $req = new Response($meta->success, $meta->http_code, '');
        $feed = new Feed;
        $feed->fromReq($req);
        return $feed;
    }

    private function __getTtwid(): string {
        $res = $this->sender->sendHead('https://www.tiktok.com');
        $cookies = Curl::extractCookies($res['data']);
        return $cookies['ttwid'] ?? '';
    }
}
