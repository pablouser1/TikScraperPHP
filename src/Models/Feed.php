<?php
namespace TikScraper\Models;
use TikScraper\Constants\Responses;

class Feed extends Base {
    public Meta $meta;
    public array $items = [];
    public bool $hasMore = false;
    public int $cursor = 0;

    /**
     * Build feed from TikTok response
     * @param \TikScraper\Models\Response $req TikTok response
     * @return \TikScraper\Models\Feed
     */
    public static function fromReq(Response $req): self {
        $feed = new Feed;
        $feed->setMeta($req);
        if ($feed->meta->success) {
            $data = $req->jsonBody;

            // Videos
            if (isset($data->itemList)) {
                $feed->setItems($data->itemList);
            // Comments
            } else if (isset($data->comments)) {
                $feed->setItems($data->comments);
            // user info list
            } else if (isset($data->userInfoList)) {
                $items = self::_buildUserInfoList($data);
                $feed->setItems($items);
            }

            // Nav
            $hasMore = false;
            if (isset($data->hasMore)) {
                $hasMore = $data->hasMore;
            }

            // Cursor (named offset in following)
            $cursor = 0;
            if (isset($data->cursor)) {
                $cursor = $data->cursor;
            } else if (isset($data->offset)) {
                // Check if reached end
                $cursor = intval($data->offset);

                $hasMore = $cursor !== 0;
            }

            $feed->setNav($hasMore, $cursor);
        }

        return $feed;
    }

    public static function fromObj(object $cache): self {
        $feed = new Feed;
        $feed->setMeta(Responses::ok());
        $feed->setItems($cache->items);
        $feed->setNav($cache->hasMore, $cache->cursor);
        return $feed;
    }

    private static function _buildUserInfoList(object $data): array {
        $items = [];
        foreach ($data->userInfoList as $userInfo) {
            $tmpInfo = (object) [
                "detail" => $userInfo->user,
                "stats" => $userInfo->stats
            ];

            $tmpFeed = (object) [
                "items" => $userInfo->itemList,
                "cursor" => 0,
                "hasMore" => false
            ];

            $items[] = (object) [
                "info" => $tmpInfo,
                "feed" => $tmpFeed
            ];
        }

        return $items;
    }

    private function setMeta(Response $res) {
        $this->meta = new Meta($res);
    }

    private function setNav(bool $hasMore, int $cursor) {
        $this->hasMore = $hasMore;
        $this->cursor = $cursor;
    }

    private function setItems(array $items) {
        $this->items = $items;
    }
}
