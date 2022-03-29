<?php
namespace TikScraper\Helpers;

class Misc {
    /**
     * Creates device_id
     */
    public static function makeId(): string {
        $characters = '0123456789';
        $randomString = '';
        $n = 19;
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }

    public static function verify_fp(): string {
        // TODO, ADD PROPER VERIFY_FP METHOD
        return 'verify_e6d8d4a90c859dfc33feefc618ea6c33';
    }

    static public function generateRandomString($length = 10): string {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function string_between(string $string, string $start, string $end): string {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini === 0) {
            return '';
        }

        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public static function normalize($string) {
        $string = preg_replace("/([^a-z0-9])/", "-", strtolower($string));
        $string = preg_replace("/(\s+)/", "-", strtolower($string));
        $string = preg_replace("/([-]+){2,}/", "-", strtolower($string));
        return $string;
    }

    /**
     * Converts TikTok's /node response to /api
     * @link https://github.com/ssovit/TikTok-API-PHP/blob/82ce93fb8ed1536bd62bf51c4c99b8007fa884a1/lib/TikTok/Helper.php#L49
     */
    public static function parseLegacyItems(array $items) {
        $final = [];
        foreach ($items as $item) {
            $parsed = (object) [
                "id" => @$item->itemInfos->id,
                "desc" => @$item->itemInfos->text,
                "createTime" => @$item->itemInfos->createTime,
                "video" => (object) [
                    "id" => @$item->itemInfos->video->id,
                    "height" => @$item->itemInfos->video->videoMeta->height,
                    "width" => @$item->itemInfos->video->videoMeta->width,
                    "duration" => @$item->itemInfos->video->videoMeta->duration,
                    "ratio" => @$item->itemInfos->video->videoMeta->height,
                    "cover"  => @$item->itemInfos->covers[0],
                    "originCover" => @$item->itemInfos->coversOrigin[0],
                    "dynamicCover" => @$item->itemInfos->coversDynamic[0],
                    "playAddr" => @$item->itemInfos->video->urls[0],
                    "downloadAddr" => @$item->itemInfos->video->urls[0]
                ],
                "author" => (object) [
                    "id" => @$item->authorInfos->userId,
                    "uniqueId" => @$item->authorInfos->uniqueId,
                    "nickname" => @$item->authorInfos->nickName,
                    "avatarThumb" => @$item->authorInfos->covers[0],
                    "avatarMedium" => @$item->authorInfos->coversMedium[0],
                    "avatarLarger" => @$item->authorInfos->coversLarger[0],
                    "signature" => @$item->authorInfos->signature,
                    "verified" => @$item->authorInfos->verified,
                    "secUid" => @$item->authorInfos->secUid
                ],
                "music" => (object) [
                    "id" => @$item->musicInfos->musicId,
                    "title" => @$item->musicInfos->musicName,
                    "playUrl" => @$item->musicInfos->playUrl[0],
                    "coverThumb" => @$item->musicInfos->covers[0],
                    "coverMedium" => @$item->musicInfos->coversMedium[0],
                    "coverLarge" => @$item->musicInfos->coversLarger[0],
                    "authorName" => @$item->musicInfos->authorName,
                    "original" => @$item->musicInfos->original
                ],
                "stats" => (object) [
                    "diggCount" => @$item->itemInfos->diggCount,
                    "shareCount" => @$item->itemInfos->shareCount,
                    "commentCount" => @$item->itemInfos->commentCount,
                    "playCount" => @$item->itemInfos->playCount
                ],
                "challenges" => [],
                "originalItem" => @$item->itemInfos->isOriginal,
                "officalItem" => @$item->itemInfos->isOfficial,
                "secret" => @$item->itemInfos->secret,
                "forFriend" => @$item->itemInfos->forFriend,
                "digged" => @$item->itemInfos->liked,
                "itemCommentStatus" => @$item->itemInfos->commentStatus,
                "showNotPass" => @$item->itemInfos->showNotPass,
                "vl1" => false
            ];

            if (isset($item->challengeInfoList)) {
                $parsed->challenges = array_map(function ($challenge) {
                    return (object) [
                        'id' => $challenge->challengeId,
                        'title' => $challenge->challengeName,
                        'desc' => $challenge->text
                    ];
                }, $item->challengeInfoList);
            }
            $final[] = $parsed;
        }
        return $final;
    }
}
